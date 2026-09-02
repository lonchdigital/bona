<?php

namespace App\Http\Actions\Admin\Instagram;

use App\Http\Actions\Admin\BaseAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BeginInstagramOAuthAction extends BaseAction
{
    public function __invoke(Request $request): RedirectResponse
    {
        $appId = (string) config('services.instagram.app_id');
        abort_if($appId === '', 503, 'Instagram OAuth is not configured.');

        $state = Str::random(64);
        $request->session()->put('instagram_oauth_state', $state);

        $url = 'https://www.instagram.com/oauth/authorize?'.http_build_query([
            'client_id' => $appId,
            'redirect_uri' => route('admin.instagram.callback'),
            'scope' => 'instagram_business_basic',
            'response_type' => 'code',
            'force_reauth' => 'true',
            'state' => $state,
        ]);

        return redirect()->away($url);
    }
}
