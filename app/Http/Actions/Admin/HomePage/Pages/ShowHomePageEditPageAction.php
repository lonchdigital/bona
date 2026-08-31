<?php

namespace App\Http\Actions\Admin\HomePage\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\HomePage\HomePageService;

class ShowHomePageEditPageAction extends BaseAction
{
    public function __invoke(
        HomePageService $homePageService,
    ) {
        $config = $homePageService->getHomePageConfig();
        $selectedCatalogItems = collect(json_decode($config?->product_types ?? '[]', true) ?: [])
            ->map(fn (mixed $selection) => (string) $selection)
            ->values();
        $selectedPopularProducts = $homePageService->getHomePageBestSalesProducts();

        if ($selectedPopularProducts->isEmpty()) {
            $selectedPopularProducts = $homePageService->getHomePageNewProducts();
        }

        return view('pages.admin.home-page.edit', [
            'allCatalogOptions' => $homePageService->getHomePageCatalogOptions(),
            'selectedCatalogItems' => $selectedCatalogItems,
            'config' => $config,
            'styleSection' => $homePageService->getHomePageStyleSection(),
            'contentSections' => $homePageService->getHomePageContentSections(),
            'selectedBestSalesProducts' => $selectedPopularProducts,
            'slides' => $homePageService->getHomePageSlides(),
            'brands' => $homePageService->getHomePageBrands(),
            'testimonials' => $homePageService->getHomePageTestimonials(),
            'faqs' => $homePageService->getHomePageFaqs(),
            'seoText' => $homePageService->getHomePageSeoText(),
        ]);
    }
}
