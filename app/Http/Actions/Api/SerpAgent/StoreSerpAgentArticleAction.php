<?php

namespace App\Http\Actions\Api\SerpAgent;

use App\Http\Requests\Api\SerpAgent\StoreSerpAgentArticleRequest;
use App\Services\SerpAgent\Exceptions\SerpAgentException;
use App\Services\SerpAgent\SerpAgentArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class StoreSerpAgentArticleAction
{
    public function __invoke(
        StoreSerpAgentArticleRequest $request,
        SerpAgentArticleService $serpAgentArticleService,
    ): JsonResponse {
        $dto = $request->toDTO();

        // "Save & Test" in the Serp Agent panel only checks that the endpoint
        // answers and sends no article along with it.
        if ($dto->isConnectivityCheck()) {
            return response()->json([
                'success' => true,
                'message' => 'Webhook is reachable and the secret is valid.',
            ]);
        }

        try {
            $result = $serpAgentArticleService->storeArticle($dto);
        } catch (SerpAgentException $exception) {
            Log::warning('SerpAgent: article rejected. ' . $exception->getMessage(), [
                'slug' => $dto->slug,
                'external_id' => $dto->externalId,
            ]);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], $exception->status());
        } catch (Throwable $throwable) {
            Log::error('SerpAgent: article could not be stored. ' . $throwable->getMessage(), [
                'slug' => $dto->slug,
                'external_id' => $dto->externalId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'The article could not be stored, see the application log for details.',
            ], 500);
        }

        Log::info('SerpAgent: article ' . $result['action'] . '.', $result);

        return response()->json([
            'success' => true,
            'message' => 'Article ' . $result['action'] . '.',
            'data' => $result,
        ], $result['action'] === 'created' ? 201 : 200);
    }
}
