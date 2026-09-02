<?php

namespace App\Http\Actions\Admin\Instagram;

use App\Http\Actions\Admin\BaseAction;
use App\Models\ApplicationConfig;
use App\Services\Instagram\InstagramCredentialStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CompleteInstagramOAuthAction extends BaseAction
{
    public function __invoke(Request $request, InstagramCredentialStore $credentials): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull('instagram_oauth_state', '');
        $state = (string) $request->query('state', '');
        abort_if($expectedState === '' || strlen($state) !== 64 || ! hash_equals($expectedState, $state), 403);

        if ($request->filled('error')) {
            return redirect()
                ->route('admin.application-config.edit.page')
                ->withErrors(['instagram' => 'Підключення Instagram скасовано. Жодних даних не змінено.']);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:2048'],
        ]);

        $appId = (string) config('services.instagram.app_id');
        $appSecret = (string) config('services.instagram.app_secret');
        $graphVersion = (string) config('services.instagram.graph_version', 'v26.0');
        abort_if($appId === '' || $appSecret === '', 503, 'Instagram OAuth is not configured.');

        try {
            $shortTokenResponse = Http::asForm()
                ->acceptJson()
                ->timeout(10)
                ->post('https://api.instagram.com/oauth/access_token', [
                    'client_id' => $appId,
                    'client_secret' => $appSecret,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => route('admin.instagram.callback'),
                    'code' => $validated['code'],
                ])
                ->throw();

            $shortToken = $shortTokenResponse->json('access_token')
                ?? $shortTokenResponse->json('data.0.access_token');
            $instagramAccountId = $shortTokenResponse->json('user_id')
                ?? $shortTokenResponse->json('data.0.user_id');

            if (! is_string($shortToken) || $shortToken === '') {
                throw new \RuntimeException('Instagram did not return an access token.');
            }

            $longTokenResponse = Http::acceptJson()
                ->timeout(10)
                ->get('https://graph.instagram.com/access_token', [
                    'grant_type' => 'ig_exchange_token',
                    'client_secret' => $appSecret,
                    'access_token' => $shortToken,
                ])
                ->throw();

            $longToken = $longTokenResponse->json('access_token');
            $expiresIn = (int) $longTokenResponse->json('expires_in', 5_184_000);

            if (! is_string($longToken) || $longToken === '') {
                throw new \RuntimeException('Instagram did not return a long-lived token.');
            }

            $profileResponse = Http::acceptJson()
                ->timeout(10)
                ->get("https://graph.instagram.com/{$graphVersion}/me", [
                    'fields' => 'user_id,username',
                    'access_token' => $longToken,
                ])
                ->throw();

            $instagramAccountId = $profileResponse->json('user_id')
                ?? $profileResponse->json('data.0.user_id')
                ?? $instagramAccountId;
            $instagramUsername = $profileResponse->json('username')
                ?? $profileResponse->json('data.0.username');

            if (! is_scalar($instagramAccountId) || (string) $instagramAccountId === '') {
                throw new \RuntimeException('Instagram did not return a professional account ID.');
            }

            if (! is_string($instagramUsername) || $instagramUsername === '') {
                throw new \RuntimeException('Instagram did not return a username.');
            }

            $preferredUsername = $this->preferredUsername();

            if ($preferredUsername !== '' && strcasecmp($instagramUsername, $preferredUsername) !== 0) {
                return redirect()
                    ->route('admin.application-config.edit.page')
                    ->withErrors([
                        'instagram' => "Ви авторизували @{$instagramUsername}, але в налаштуваннях сайту вказано @{$preferredUsername}. Повторіть підключення з правильним профілем.",
                    ]);
            }

            $credentials->store(
                $longToken,
                (string) $instagramAccountId,
                $instagramUsername,
                max($expiresIn, 1),
            );

            Cache::forget('instagram_feed');
            Cache::forget('instagram_feed_stale');

            return redirect()
                ->route('admin.application-config.edit.page')
                ->with('success', 'Instagram підключено. Стрічка на головній сторінці оновиться автоматично.');
        } catch (Throwable $exception) {
            Log::error('Instagram OAuth failed.', [
                'exception' => $exception::class,
                'code' => $exception->getCode(),
            ]);

            return redirect()
                ->route('admin.application-config.edit.page')
                ->withErrors(['instagram' => 'Не вдалося підключити Instagram. Перевірте, що це професійний профіль і що він доданий до застосунку Meta.']);
        }
    }

    private function preferredUsername(): string
    {
        $profileUrl = (string) ApplicationConfig::where('config_name', 'instagram')->value('config_data');
        $path = trim((string) parse_url($profileUrl, PHP_URL_PATH), '/');

        return ltrim(explode('/', $path)[0] ?? '', '@');
    }
}
