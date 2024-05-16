<?php

namespace App\Services\Cart;

use App\DataClasses\DeliveryTypesDataClass;
use App\Models\Cart;
use App\Models\CartProducts;
use App\Models\ProductGalleries;
use App\Models\PromoCode;
use App\Models\User;
use App\Models\Product;
use App\Models\Color;
use App\Models\WishList;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Cart\DTO\AddPromoCodeToCartDTO;
use App\Services\Cart\DTO\ChangeProductCountInCartDTO;
use App\Services\Cart\DTO\DeleteProductFromCartDTO;
use App\Services\Cart\DTO\GetProductsSummaryWithDeliveryDTO;
use App\Services\WishList\WishListService;
use Illuminate\Support\Collection;

class CartService extends BaseService
{

    public function __construct(
        private readonly WishListService $wishListService,
    ) { }

    public function getCartForGuestUser(string $token):? Cart
    {
        return Cart::where('token', $token)->first();
    }

    public function getCartForAuthUser(User $user): ?Cart
    {
        return Cart::where('user_id', $user->id)->first();
    }

    public function createCartByToken(string $token): Cart
    {
        return Cart::create([
            'token' => $token,
        ]);
    }

    public function createCartByUser(User $user): Cart
    {
        return Cart::create([
            'user_id' => $user->id,
        ]);
    }

    public function isProductInCart(Product $product, Cart $cart): bool
    {
        return $cart->products()->where('product_id', $product->id)->exists();
    }

    public function getCountOfSpecificProduct(Product $product, Cart $cart): int
    {
        return $cart->products()->where('product_id', $product->id)->first()->pivot->count;
    }

    public function getCountOfProductsInCart(Cart $cart): int
    {
        return $cart->products()->count();
    }

    public function getProductsInCart(Cart $cart): Collection
    {
        return $cart->products;
    }

    public function getAttributesWithOptions(int $product_id, $productType): array
    {
        $attributeOptions = [];

        $currentAttributeOptions = $productType->attributes()
            ->with(['productAttributeOptions' => function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            }])
            ->get();

        if(count($currentAttributeOptions)) {
            foreach ($currentAttributeOptions as $attribute) {
                $atr_options = [];
                foreach ($attribute->productAttributeOptions as $attributeOption){
                    $atr_options[] = $attributeOption;
                }
                $attributeOptions[$attribute->id] = $atr_options;
            }
        }

        return $attributeOptions;
    }

    public function addProductToCart(Cart $cart, Product $product, ChangeProductCountInCartDTO $request): void
    {
        $allProductVariations = CartProducts::where('cart_id', $cart->id)->where('product_id', $product->id)->get();
        $requestProductAttributes = $request->productAttributes;
        $isProductInCart = false;

        foreach ($allProductVariations as $allProductVariation) {
            $difference = array_diff_assoc(json_decode($allProductVariation['attributes'], true), $requestProductAttributes);

            if(empty($difference)) {
                $count = $allProductVariation->count + 1;
                $allProductVariation->update(['count' => $count]);

                $isProductInCart = true;
                break;
            }
        }

        if( !$isProductInCart ) {
            $attributeOptions = $this->getAttributesWithOptions($product->id, $product->productType);

            $productAttributeColor['color'] = $requestProductAttributes['color'];
            unset($requestProductAttributes['color']);

            $productAttributesSum = [];
            foreach ($requestProductAttributes as $attributeKey => $productAttributeName ) {
                if( !is_null($productAttributeName) ) {
                    $productAtrID = preg_replace('/[^0-9]/', '', $attributeKey);
                    $productAttributesSum[] = collect($attributeOptions[$productAtrID])->firstWhere('name', $productAttributeName)->price;
                }
            }

            if( !is_null($productAttributeColor['color']) ) {
                $color_price = $product->colors->firstWhere('name', $productAttributeColor['color'])->pivot->price;
                if( is_numeric($color_price) || is_float($color_price) )
                    $productAttributesSum[] = $color_price;
            }

            $productAttributesSum = array_sum($productAttributesSum);


            $color = Color::where(function ($query) use ($productAttributeColor) {
                $query->whereJsonContains('name', ['uk' => $productAttributeColor['color']])
                    ->orWhereJsonContains('name', ['ru' => $productAttributeColor['color']]);
            })->first();


            $cart->products()->attach([$product->id => [
                'count' => $request->productCount,
                'price' => $product->price,
                'attributes' => json_encode($request->productAttributes),
                'attributes_price' => $productAttributesSum,
                'current_image_path' => ProductGalleries::where('product_id', $product->id)->where('color_id', $color->id)->first()->image_path ?? null
            ]]);
        }

    }

    public function addSubProductToCart(Cart $cart, Product $product, ChangeProductCountInCartDTO $request): void
    {
        if (!$cart->products()->where('product_id', $product->id)->exists()) {
            $cart->products()->attach([$product->id => ['count' => $request->productCount, 'price' => $product->price]]);
        } else {
            $productCount = $cart->products()->where('product_id', $product->id)->first()->pivot->count;
            $cart->products()->updateExistingPivot($product->id, ['count' => $productCount + $request->productCount]);
        }
    }

    public function changeProductCount(Cart $cart, Product $product, ChangeProductCountInCartDTO $request): void
    {
        if( !is_null($request->productAttributes) ) { // all sub products have productAttributes as null

            $allProductVariations = CartProducts::where('cart_id', $cart->id)->where('product_id', $product->id)->get();
            $requestProductAttributes = $request->productAttributes;

            foreach ($allProductVariations as $allProductVariation) {
                $difference = array_diff_assoc(json_decode($allProductVariation['attributes'], true), $requestProductAttributes);

                if(empty($difference)) {
                    $allProductVariation->update(['count' => $request->productCount]);
                    break;
                }
            }

        } else {

            if ($cart->products()->where('product_id', $product->id)->exists()) {
                $cart->products()->updateExistingPivot($product->id, ['count' => $request->productCount]);
            }

        }

    }

    public function deleteProductFromCart(Cart $cart, Product $product, DeleteProductFromCartDTO $request): void
    {
        if( !is_null($request->productAttributes) ) { // all sub products have productAttributes as null

            $allProductVariations = CartProducts::where('cart_id', $cart->id)->where('product_id', $product->id)->get();
            $requestProductAttributes = $request->productAttributes;

            foreach ($allProductVariations as $allProductVariation) {
                $difference = array_diff_assoc(json_decode($allProductVariation['attributes'], true), $requestProductAttributes);

                if(empty($difference)) {
                    $allProductVariation->delete();
                    break;
                }
            }

        } else {
            $cart->products()->detach($product->id);
        }

    }

    public function getSummary(Cart $cart, ?WishList $wishList): array
    {
        $totalPrice = 0;
        foreach ($cart->products as $product) {
            // TODO: we counted products without attributes. Do we need to do it?
//            $totalPrice += $product->pivot->price * $product->pivot->count;
            $totalPrice += ( $product->pivot->price + $product->pivot->attributes_price ) * $product->pivot->count;
        }

        $productsInWishList = $this->wishListService->getWishListProductsId($wishList);

        $cart->products->map(function ($product) use($productsInWishList) {
            $product->is_in_wish_list = $productsInWishList->contains($product->id);
        });

        $hasFreeDelivery = false;

        if ($totalPrice >= config('domain.free_delivery_from_price')) {
            $hasFreeDelivery = true;
        }


        $discount = 0;

        if ($cart->promoCode) {
            $discount = $totalPrice / 100 * $cart->promoCode->discount;
            $totalPrice = $totalPrice - $discount;
        }

        return [

            'summary' => [
                'products' =>  round($totalPrice, 2),
                'total' => round($totalPrice, 2),
                'discount' => round($discount, 2),
//                'products_with_attributes' => round($discount, 2),
            ],
            'has_free_delivery' => $hasFreeDelivery,
            'promo_code' => $cart->promoCode,
        ];
    }

    public function getCartSummary(Cart $cart): array
    {
        $totalPrice = 0;
        $allProductsWithVariations = CartProducts::where('cart_id', $cart->id)->get();

        foreach ($allProductsWithVariations as $productInCart) {
            $totalPrice += ($productInCart->price + $productInCart->attributes_price) * $productInCart->count;
        }
        /*foreach ($cart->products as $product) {
            $totalPrice += $product->pivot->price * $product->pivot->count;
        }*/

        $hasFreeDelivery = false;
        /*if ($totalPrice >= config('domain.free_delivery_from_price')) {
            $hasFreeDelivery = true;
        }*/

        $discount = 0;
        /*if ($cart->promoCode) {
            $discount = $totalPrice / 100 * $cart->promoCode->discount;
            $totalPrice = $totalPrice - $discount;
        }*/

        return [
            'summary' => [
                'products' =>  round($totalPrice, 2),
                'total' => round($totalPrice, 2),
                'discount' => round($discount, 2),
            ],
            'has_free_delivery' => $hasFreeDelivery,
            'promo_code' => $cart->promoCode,
        ];
    }

    public function getProductsInCartWithSummary(Cart $cart, ?WishList $wishList): array
    {
        $summary = $this->getCartSummary($cart);
//        $summary = $this->getSummary($cart, $wishList);
        $summary['products'] = $cart->products;

        return $summary;
    }

    public function attachPromoCode(AddPromoCodeToCartDTO $request, Cart $cart): ServiceActionResult
    {
        $code = PromoCode::where('code', $request->code)->first();

        if ($code->is_used) {
            return ServiceActionResult::make(false, trans('base.promo_code_already_used'));
        }

        $cart->update([
            'promo_code_id' => $code->id,
        ]);

        return ServiceActionResult::make(true, trans('base.promo_code_add_success'));
    }

    public function getCartSummaryWithDelivery(GetProductsSummaryWithDeliveryDTO $request, Cart $cart, ?WishList $wishList): array
    {
        $deliveryPrice = 0;
        $isCarrier = false;

        if ($request->deliveryTypeId === DeliveryTypesDataClass::ADDRESS_DELIVERY) {
            $deliveryPrice = config('domain.delivery_price');
        } else if ($request->deliveryTypeId === DeliveryTypesDataClass::NP_DELIVERY) {
            $isCarrier = true;
        } else if ($request->deliveryTypeId === DeliveryTypesDataClass::MIST_EXPRESS_DELIVERY) {
            $isCarrier = true;
        }

        $summary = $this->getSummary($cart, $wishList);

        $deliveryOld = 0;
        if ($deliveryPrice > 0 && $summary['summary']['products'] > config('domain.free_delivery_from_price')) {
            $deliveryOld = $deliveryPrice;
            $deliveryPrice = 0;
        }

        $summary['summary']['is_carrier'] = $isCarrier;
        $summary['summary']['delivery_old'] = $deliveryOld;
        $summary['summary']['delivery'] = $deliveryPrice;

        return $summary;
    }

}
