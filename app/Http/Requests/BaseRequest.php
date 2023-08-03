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
        return mb_strtolower($trans) . ' ' . mb_strtoupper($languageCode);
    }

    public function baseRules(): array
    {
        return [];
    }

    public abstract function toDTO(): BaseDTO;
}
