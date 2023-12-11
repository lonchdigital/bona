<?php

namespace App\Http\Actions\Store\AboutUsPage\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\AboutUsPage\AboutUsPageService;
use App\Services\Brand\BrandService;
use App\Services\BlogArticle\BlogArticleService;

class ShowAboutUsPageAction extends BaseAction
{
    public function __invoke(
        AboutUsPageService       $aboutUsPageService,
        BrandService             $brandService,
        BlogArticleService       $blogArticleService,
    )
    {
        return view('pages.store.about-us-page', [
            'aboutUsConfig' => $aboutUsPageService->getAboutUsConfig(),
            'brands' => $brandService->getBrands(),
            'articles' => $blogArticleService->getLatestArticles(3),
        ]);
    }
}
