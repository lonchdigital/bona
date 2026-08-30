<?php

namespace App\Http\Actions\Admin\Instagram;

use App\Http\Actions\Admin\BaseAction;
use App\Models\ApplicationConfig;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class CompleteInstagramOAuthAction extends BaseAction
{
    public function __invoke(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:2048'],
            'state' => ['required', 'string', 'size:64'],
        ]);

        $expectedState = (string) $request->session()->pull('instagram_oauth_state', '');
        abort_if($expectedState === '' || ! hash_equals($expectedState, $validated['state']), 403);

        $appId = (string) config('services.facebook.client_id');
        $appSecret = (string) config('services.facebook.client_secret');
        abort_if($appId === '' || $appSecret === '', 503, 'Instagram OAuth is not configured.');

        try {
            $shortResponse = $client->get('https://graph.facebook.com/v19.0/oauth/access_token', [
                'query' => [
                    'client_id' => $appId,
                    'redirect_uri' => route('admin.instagram.callback'),
                    'client_secret' => $appSecret,
                    'code' => $validated['code'],
                ],
            ]);
            $shortToken = json_decode($shortResponse->getBody()->getContents(), true)['access_token'] ?? null;

            if (! is_string($shortToken) || $shortToken === '') {
                throw new \RuntimeException('Facebook did not return an access token.');
            }

            $longResponse = $client->get('https://graph.facebook.com/v19.0/oauth/access_token', [
                'query' => [
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => $appId,
                    'client_secret' => $appSecret,
                    'fb_exchange_token' => $shortToken,
                ],
            ]);
            $longToken = json_decode($longResponse->getBody()->getContents(), true)['access_token'] ?? null;

            if (! is_string($longToken) || $longToken === '') {
                throw new \RuntimeException('Facebook did not return a long-lived token.');
            }

            ApplicationConfig::updateOrCreate(
                ['config_name' => 'instagramAccessToken'],
                ['config_data' => $longToken],
            );
            Cache::forget('instagram_feed');

            return redirect()
                ->route('admin.application-config.edit.page')
                ->with('success', 'Instagram access was updated.');
        } catch (Throwable $exception) {
            Log::error('Instagram OAuth failed.', [
                'exception' => $exception::class,
                'code' => $exception->getCode(),
            ]);

            return redirect()
                ->route('admin.application-config.edit.page')
                ->withErrors(['instagram' => 'Instagram authorization failed. Please try again.']);
        }
    }
}
