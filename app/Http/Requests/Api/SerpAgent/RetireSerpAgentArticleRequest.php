<?php

namespace App\Http\Requests\Api\SerpAgent;

use App\Http\Requests\BaseRequest;
use App\Services\SerpAgent\DTO\SerpAgentRetirementDTO;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RetireSerpAgentArticleRequest extends BaseRequest
{
    public const EVENT = 'article.retire';

    /** Shares the publication endpoint; only the "event" field tells the two apart. */
    public static function isRetirement(Request $request): bool
    {
        return $request->input('event') === self::EVENT;
    }

    public function authorize(): bool
    {
        // Authentication is done by the VerifySerpAgentWebhook middleware.
        return true;
    }

    public function rules(): array
    {
        return [
            'event' => ['required', Rule::in([self::EVENT])],
            'deliveryId' => ['required', 'string', 'max:191'],
            'slug' => ['required', 'string', 'max:191'],
            'language' => ['required', Rule::in($this->availableLanguages)],
            'url' => ['nullable', 'string', 'max:2048'],
            // Sent as null on purpose for a 410, so a missing field is not taken for one.
            'redirectTo' => ['present', 'nullable', 'string', 'url:http,https', 'max:2048'],
            'test' => ['nullable', 'boolean'],
        ];
    }

    public function toDTO(): SerpAgentRetirementDTO
    {
        return new SerpAgentRetirementDTO(
            deliveryId: trim($this->input('deliveryId')),
            slug: trim($this->input('slug')),
            locale: $this->input('language'),
            url: $this->input('url'),
            redirectTo: $this->input('redirectTo'),
            isTest: $this->boolean('test'),
        );
    }

    /**
     * Serp Agent shows the message to the operator as it is, so it carries the
     * first problem in words rather than a generic "invalid payload".
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422));
    }
}
