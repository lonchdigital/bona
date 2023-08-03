<?php

namespace App\Services\Product;

use App\Models\User;
use App\Models\Product;
use App\Services\Base\BaseService;
use Illuminate\Support\Collection;
use App\Services\WishList\WishListService;
use App\DataClasses\ProductFieldTypeOptionsDataClass;


class SimilarProductsService extends BaseService
{
    public function __construct(
        private readonly WishListService $wishListService
    ) { }

    public function getSimilarProductsPaginated(?User $user, Product $product, int $page = 1): \LaravelIdea\Helper\App\Models\_IH_Product_C|\Illuminate\Pagination\LengthAwarePaginator|array
    {
        $selectColumns = ['*'];
        $selectColumnsBindings = [];
        $orderByColumns = [];

        foreach ($product->productType->fields as $customField) {

            $value = $product->getCustomFieldValue($customField->id);
            $fieldName = 'same_custom_field_' . $customField->id;

            if ($customField->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_STRING) {
                $selectColumns[] = \DB::raw('JSON_CONTAINS(JSON_EXTRACT(custom_fields, ?), ?) as ' . $fieldName);
                $selectColumnsBindings[] = '$."' . $customField->id . '"';
                $selectColumnsBindings[] = (string)$value;
            } elseif ($customField->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER ||
                $customField->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE
            ) {
                $selectColumns[] = \DB::raw('CAST(JSON_EXTRACT(custom_fields, ?) AS DECIMAL(2)) >= ? as ' . $fieldName);
                $selectColumnsBindings[] = '$."' . $customField->id . '"';
                $selectColumnsBindings[] = $value;
            } elseif ($customField->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {
                if ($customField->is_multiselectable) {
                    foreach ($value as $singleValue) {
                        $selectColumns[] = \DB::raw('JSON_CONTAINS(JSON_EXTRACT(custom_fields, ?), ?) as ' . $fieldName);
                        $selectColumnsBindings[] = '$."' . $customField->id . '"';
                        $selectColumnsBindings[] = (string)$singleValue;
                    }
                } else {
                    $selectColumns[] = \DB::raw('JSON_CONTAINS(JSON_EXTRACT(custom_fields, ?), ?) as ' . $fieldName);
                    $selectColumnsBindings[] = '$."' . $customField->id . '"';
                    $selectColumnsBindings[] = (string)$value;
                }
            }

            $orderByColumns[] = $fieldName;

        }

        if ($product->productType->has_color) {
            $fieldName = 'same_color';
            $selectColumns[] = \DB::raw('(exists (select * from products as products_by_color where products.id = products_by_color.parent_product_id and main_color_id = ?) OR main_color_id = ?) as ' . $fieldName);
            $selectColumnsBindings[] = $product->main_color_id;
            $selectColumnsBindings[] = $product->main_color_id;
            $orderByColumns[] = $fieldName;
        }

        if ($product->productType->has_length) {
            $fieldName = 'same_length';
            $selectColumns[] = \DB::raw('length = ? as ' . $fieldName);
            $selectColumnsBindings[] = $product->length;
            $orderByColumns[] = $fieldName;
        }

        if ($product->productType->has_width) {
            $fieldName = 'same_width';
            $selectColumns[] = \DB::raw('width = ? as ' . $fieldName);
            $selectColumnsBindings[] = $product->width;
            $orderByColumns[] = $fieldName;
        }

        if ($product->productType->has_height) {
            $fieldName = 'same_height';
            $selectColumns[] = \DB::raw('width = ? as ' . $fieldName);
            $selectColumnsBindings[] = $product->height;
            $orderByColumns[] = $fieldName;
        }

        $query = Product::select($selectColumns);
        $query->setBindings($selectColumnsBindings, 'select');
        $query->where('id', '!=', $product->id);

        foreach ($orderByColumns as $orderByColumn) {
            $query->orderByDesc($orderByColumn);
        }

        $products = $query->paginate(4);

        $products->setCollection($this->mapInWishList($user, $products->getCollection()));

        return $products;
    }

    private function mapInWishList(?User $user, Collection $products): Collection
    {
        if ($user) {
            $wishList = $this->wishListService->getWishListByUser($user);
            $productsInWishList = $this->wishListService->getProductsByWishList($wishList);

            $products->map(function ($product) use($productsInWishList) {
                $product->is_in_wish_list = $productsInWishList->contains($product->id);
            });
        } else {
            $products->map(function ($product) {
                $product->is_in_wish_list = false;
            });
        }

        return $products;
    }
}
