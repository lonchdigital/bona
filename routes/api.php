<?php

use App\Http\Actions\Api\SerpAgent\StoreSerpAgentArticleAction;
use App\Http\Middleware\VerifySerpAgentWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Serp Agent — Custom API integration
|--------------------------------------------------------------------------
|
| Webhook URL to enter in the "Custom API" modal on app.serp-agent.com:
|
|     POST https://<domain>/api/serp-agent/articles
|
| Authenticated with "Authorization: Bearer <SERP_AGENT_WEBHOOK_SECRET>".
| The GET route below is a convenience check for that same secret.
|
*/

Route::prefix('serp-agent')
    ->middleware(VerifySerpAgentWebhook::class)
    ->group(function () {
        Route::name('api.serp-agent.articles.store')->post('/articles', StoreSerpAgentArticleAction::class);

        Route::name('api.serp-agent.ping')->get('/ping', function () {
            return response()->json([
                'success' => true,
                'message' => 'Serp Agent webhook is reachable and the secret is valid.',
            ]);
        });
    });
