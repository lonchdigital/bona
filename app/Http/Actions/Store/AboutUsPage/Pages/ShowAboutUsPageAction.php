<?php

namespace App\Http\Actions\Store\AboutUsPage\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\AboutUsConfig;
use App\Services\AboutUsPage\AboutUsPageService;
use App\Services\BlogArticle\BlogArticleService;
use App\Services\Brand\BrandService;
use App\Support\LastModified;

class ShowAboutUsPageAction extends BaseAction
{
    public function __invoke(
        AboutUsPageService $aboutUsPageService,
        BrandService $brandService,
        BlogArticleService $blogArticleService,
    ) {
        $config = $aboutUsPageService->getAboutUsConfig() ?? new AboutUsConfig;
        $config->meta_tags = $this->handleFollowTag($config->meta_tags);

        LastModified::set($config->updated_at);

        return view('pages.store.about-us-page', [
            'aboutUsConfig' => $config,
            'brands' => $brandService->getBrands(),
            'articles' => $blogArticleService->getLatestArticles(3),
            // Each block renders only when it has something in it, so the page
            // can be filled in a piece at a time.
            'aboutUsFacts' => $aboutUsPageService->getFacts(),
            'aboutUsSteps' => $aboutUsPageService->getSteps(),
            'aboutUsTeam' => $aboutUsPageService->getTeamMembers(),
        ]);
    }
}
