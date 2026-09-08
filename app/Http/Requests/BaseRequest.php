<?php

namespace App\Http\Requests;

use App\Services\Application\ApplicationConfigService;
use App\Services\Base\DTO\BaseDTO;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
    protected array $availableLanguages = [];

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->availableLanguages = app()->make(ApplicationConfigService::class)->getAvailableLanguages();
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    protected function prepareAttribute(string $trans, string $languageCode): string
    {
        return mb_strtolower($trans).' '.mb_strtoupper($languageCode);
    }

    public function baseRules(): array
    {
        return [];
    }

    /**
     * Keep the page that produced a lead/order without accepting an arbitrary
     * third-party link from a submitted form.
     */
    public function sourceUrl(?string $fallback = null): ?string
    {
        $candidate = trim((string) ($this->input('source_url') ?: $fallback ?: $this->headers->get('referer')));

        if ($candidate === '' || filter_var($candidate, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        if (! in_array(mb_strtolower((string) parse_url($candidate, PHP_URL_SCHEME)), ['http', 'https'], true)) {
            return null;
        }

        $sourceHost = mb_strtolower((string) parse_url($candidate, PHP_URL_HOST));
        $allowedHosts = collect([
            $this->getHost(),
            parse_url((string) config('app.url'), PHP_URL_HOST),
        ])->filter()->map(fn ($host) => mb_strtolower((string) $host));

        if (! $allowedHosts->contains($sourceHost)) {
            return null;
        }

        return mb_substr($candidate, 0, 2048);
    }

    abstract public function toDTO(): BaseDTO;
}
