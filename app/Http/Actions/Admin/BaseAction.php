<?php

namespace App\Http\Actions\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\Base\ServiceActionResult;
use App\Http\Resources\BaseActionResource;

class BaseAction
{
    public function handleActionResult(string $route, Request $request, ServiceActionResult $result): mixed
    {
        $key = $result->isSuccess() ? 'success' : 'error';

        if ($request->ajax()) {
            Session::flash($key, $result->getMessage());

            return BaseActionResource::make([
                'success' => $result->isSuccess(),
                'message' => $result->getMessage(),
                'redirect_to' => $this->staysOnPage($request, $result) ? '' : $route,
            ]);
        }

        if ($this->staysOnPage($request, $result)) {
            return redirect()->back()->with([$key => $result->getMessage()]);
        }

        return redirect($route)->with([$key => $result->getMessage()]);
    }

    /**
     * Whether saving should leave the editor where they are.
     *
     * Every admin form used to answer with the list it belongs to, so saving a
     * page threw away the place you were working in. Editing keeps you there —
     * the page is still the thing in front of you. Creating and deleting do
     * move you: there is no longer a page to stay on.
     *
     * Read off the route name rather than a flag on each of the fifty-odd
     * actions: the panel names every editing route `<thing>.edit` without
     * exception.
     */
    private function staysOnPage(Request $request, ServiceActionResult $result): bool
    {
        if (!$result->isSuccess()) {
            return true;
        }

        $routeName = $request->route()?->getName();

        return is_string($routeName) && str_ends_with($routeName, '.edit');
    }

    protected function getAuthUser(): ?User
    {
        return Auth::user();
    }


    public function handleFollowTag(string|null $meta_tags): string
    {
        if( !is_null($meta_tags) ) {
            if (str_contains($meta_tags, '%nofollow%')) {
                $meta_tags = str_replace('%nofollow%', '<meta name="robots" content="noindex, nofollow">', $meta_tags);
            } else {
                $meta_tags .= '<meta name="robots" content="index, follow">';
            }
            return $meta_tags;
        } else {
            return '<meta name="robots" content="index, follow">';
        }
    }
}
