<?php

namespace App\Http\Actions\Store\AboutUsPage\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\AboutUsPage\AboutUsPageService;
use App\Services\Brand\BrandService;
use App\Services\BlogArticle\BlogArticleService;
use Abordage\LastModified\Facades\LastModified;

class ShowAboutUsPageAction extends BaseAction
{
    public function __invoke(
        AboutUsPageService       $aboutUsPageService,
        BrandService             $brandService,
        BlogArticleService       $blogArticleService,
    )
    {
        $config = $aboutUsPageService->getAboutUsConfig();
        $config->meta_tags = $this->handleFollowTag($config->meta_tags);

        LastModified::set($config->updated_at);

        return view('pages.store.about-us-page', [
            'aboutUsConfig' => $config,
            'brands' => $brandService->getBrands(),
            'articles' => $blogArticleService->getLatestArticles(3),
        ]);
    }
}
