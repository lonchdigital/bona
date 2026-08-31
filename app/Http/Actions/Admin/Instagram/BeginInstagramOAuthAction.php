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
        $appId = (string) config('services.facebook.client_id');
        $graphVersion = (string) config('services.instagram.graph_version', 'v26.0');
        abort_if($appId === '', 503, 'Instagram OAuth is not configured.');

        $state = Str::random(64);
        $request->session()->put('instagram_oauth_state', $state);

        $url = "https://www.facebook.com/{$graphVersion}/dialog/oauth?".http_build_query([
            'client_id' => $appId,
            'redirect_uri' => route('admin.instagram.callback'),
            'scope' => 'instagram_basic,instagram_manage_insights,pages_show_list,pages_read_engagement',
            'response_type' => 'code',
            'state' => $state,
        ]);

        return redirect()->away($url);
    }
}
