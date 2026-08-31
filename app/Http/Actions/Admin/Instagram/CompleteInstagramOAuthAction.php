<?php

namespace App\Http\Actions\Admin\Instagram;

use App\Http\Actions\Admin\BaseAction;
use App\Models\ApplicationConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CompleteInstagramOAuthAction extends BaseAction
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:2048'],
            'state' => ['required', 'string', 'size:64'],
        ]);

        $expectedState = (string) $request->session()->pull('instagram_oauth_state', '');
        abort_if($expectedState === '' || ! hash_equals($expectedState, $validated['state']), 403);

        $appId = (string) config('services.facebook.client_id');
        $appSecret = (string) config('services.facebook.client_secret');
        $graphVersion = (string) config('services.instagram.graph_version', 'v26.0');
        $graphUrl = "https://graph.facebook.com/{$graphVersion}";
        abort_if($appId === '' || $appSecret === '', 503, 'Instagram OAuth is not configured.');

        try {
            $shortToken = Http::acceptJson()
                ->timeout(10)
                ->get("{$graphUrl}/oauth/access_token", [
                    'client_id' => $appId,
                    'redirect_uri' => route('admin.instagram.callback'),
                    'client_secret' => $appSecret,
                    'code' => $validated['code'],
                ])
                ->throw()
                ->json('access_token');

            if (! is_string($shortToken) || $shortToken === '') {
                throw new \RuntimeException('Facebook did not return an access token.');
            }

            $longToken = Http::acceptJson()
                ->timeout(10)
                ->get("{$graphUrl}/oauth/access_token", [
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => $appId,
                    'client_secret' => $appSecret,
                    'fb_exchange_token' => $shortToken,
                ])
                ->throw()
                ->json('access_token');

            if (! is_string($longToken) || $longToken === '') {
                throw new \RuntimeException('Facebook did not return a long-lived token.');
            }

            $pages = Http::acceptJson()
                ->timeout(10)
                ->get("{$graphUrl}/me/accounts", [
                    'fields' => 'id,name,access_token,instagram_business_account{id,username}',
                    'limit' => 100,
                    'access_token' => $longToken,
                ])
                ->throw()
                ->json('data', []);

            $instagramPages = collect($pages)->filter(
                fn (array $item): bool => filled(data_get($item, 'instagram_business_account.id'))
            );
            $preferredUsername = $this->preferredUsername();
            $page = $instagramPages->first(
                fn (array $item): bool => $preferredUsername !== ''
                    && strcasecmp((string) data_get($item, 'instagram_business_account.username'), $preferredUsername) === 0
            ) ?? $instagramPages->first();

            if (! is_array($page)) {
                throw new \RuntimeException('No connected Instagram professional account was found.');
            }

            $instagramAccountId = (string) data_get($page, 'instagram_business_account.id');
            $instagramUsername = (string) data_get($page, 'instagram_business_account.username', '');
            $pageAccessToken = (string) ($page['access_token'] ?? $longToken);

            DB::transaction(function () use ($instagramAccountId, $instagramUsername, $pageAccessToken): void {
                $values = [
                    'instagramAccessToken' => $pageAccessToken,
                    'instagramBusinessAccountId' => $instagramAccountId,
                    'instagramUsername' => $instagramUsername,
                ];

                foreach ($values as $name => $value) {
                    ApplicationConfig::updateOrCreate(
                        ['config_name' => $name],
                        ['config_data' => $value],
                    );
                }
            });

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
                ->withErrors(['instagram' => 'Не вдалося підключити Instagram. Перевірте, що профіль є професійним і прив’язаний до Facebook-сторінки.']);
        }
    }

    private function preferredUsername(): string
    {
        $profileUrl = (string) ApplicationConfig::where('config_name', 'instagram')->value('config_data');
        $path = trim((string) parse_url($profileUrl, PHP_URL_PATH), '/');

        return ltrim(explode('/', $path)[0] ?? '', '@');
    }
}
