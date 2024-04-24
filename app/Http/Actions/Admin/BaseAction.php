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
        if ($request->ajax()) {
            if (!$result->isSuccess()) {
                Session::flash('error', $result->getMessage());
            } else {
                Session::flash('success', $result->getMessage());
            }

            return BaseActionResource::make([
                'success' => $result->isSuccess(),
                'message' => $result->getMessage(),
                'redirect_to' => $route,
            ]);

        } else {
            if (!$result->isSuccess()) {
                return redirect($route)
                    ->with([
                        'error' => $result->getMessage(),
                    ]);
            }

            return redirect($route)
                ->with([
                    'success' => $result->getMessage(),
                ]);
        }
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
