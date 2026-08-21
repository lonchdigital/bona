<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifySerpAgentWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('serp-agent.enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'Serp Agent integration is disabled.',
            ], 503);
        }

        $secret = (string) config('serp-agent.secret');

        if ($secret === '') {
            Log::error('SerpAgent: SERP_AGENT_WEBHOOK_SECRET is not set, request rejected.');

            return response()->json([
                'success' => false,
                'message' => 'Webhook secret is not configured on the server.',
            ], 500);
        }

        $providedSecret = $this->extractSecret($request);

        if ($providedSecret === null || !hash_equals($secret, $providedSecret)) {
            Log::warning('SerpAgent: request rejected, invalid or missing secret.', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing webhook secret.',
            ], 401);
        }

        return $next($request);
    }

    /**
     * Serp Agent sends "Authorization: Bearer <secret>". The other headers are
     * accepted as well so the endpoint keeps working if the panel ever changes
     * the way it authenticates.
     */
    private function extractSecret(Request $request): ?string
    {
        $authorization = trim((string) $request->header('Authorization', ''));

        if ($authorization !== '') {
            if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
                return trim($matches[1]);
            }

            return $authorization;
        }

        foreach (['X-Webhook-Secret', 'X-Serp-Agent-Secret', 'X-Api-Key'] as $headerName) {
            $headerValue = $request->header($headerName);

            if (is_string($headerValue) && trim($headerValue) !== '') {
                return trim($headerValue);
            }
        }

        return null;
    }
}
