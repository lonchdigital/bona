<?php

namespace App\Http\Actions\Store\Collection\Pages;

use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Models\Collection;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Collection\CollectionService;
use App\Services\Currency\CurrencyService;
use App\Services\Product\ProductService;
use App\Services\WishList\WishListService;

class ShowCollectionPageAction extends BaseAction
{
    public function __invoke(
        Collection $collection,
        CatalogFilterRequest $request,
        ProductService $productService,
        WishListService $wishListService,
        CurrencyService $currencyService,
        CollectionService $collectionService,
    )
    {
        $wishList = null;
        if ($this->getAuthUser()) {
            $wishList = $wishListService->getWishListByUser($this->getAuthUser());
        }


        $filtersData = $request->toDTO();
        $page = $filtersData->filters['page'] ?? 1;

        $collection->load(['brand']);

        return view('pages.store.collection', [
            'collection' => $collection,
            'anotherCollections' => $collectionService->getCollectionsByBrandId($collection->brand_id),
            'filtersData' => $filtersData->filters,
            'productsPaginated' => $productService->getProductsByCollectionAndTypePaginated($collection, $request->toDTO(), $filtersData->filters['per_page'] ?? 24, $page),
            'wishListProducts' => $wishListService->getWishListProductsId($wishList),
            'baseCurrency' => $currencyService->getBaseCurrency(),
        ]);
    }
}
