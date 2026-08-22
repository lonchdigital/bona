<?php

namespace App\Http\Actions\Store\Seo;

use App\Services\Seo\LlmsTxtService;
use Illuminate\Http\Response;

class ShowLlmsTxtFileContent
{
    public function __invoke(LlmsTxtService $llmsTxtService): Response
    {
        return response($llmsTxtService->build())
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
