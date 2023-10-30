<?php

namespace App\Http\Actions\Admin\HomePage;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\HomePage\HomePageEditRequest;
use App\Services\HomePage\HomePageService;

class HomePageEditAction extends BaseAction
{
    public function __invoke(
        HomePageEditRequest $request,
        HomePageService $homePageService,
    )
    {
//        dd($request->slides);
//        dd($request->toDTO());
        $result = $homePageService->editHomePage($request->toDTO());

        return $this->handleActionResult(route('admin.home-page.edit.page'), $request, $result);
    }
}
