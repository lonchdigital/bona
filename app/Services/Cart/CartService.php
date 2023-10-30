<?php

namespace App\Services\Cart;

use App\DataClasses\DeliveryTypesDataClass;
use App\Models\Cart;
use App\Models\PromoCode;
use App\Models\User;
use App\Models\Product;
use App\Models\WishList;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Cart\DTO\AddPromoCodeToCartDTO;
use App\Services\Cart\DTO\ChangeProductCountInCartDTO;
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

    public function addProductToCart(Cart $cart, Product $product, ChangeProductCountInCartDTO $request): void
    {
        if (!$cart->products()->where('product_id', $product->id)->exists()) {
            $cart->products()->attach([$product->id => ['count' => $request->productCount, 'price' => $product->price]]);
        } else {
            $cart->products()->updateExistingPivot($product->id, ['count' => $request->productCount]);
        }
    }

    public function changeProductCount(Cart $cart, Product $product, ChangeProductCountInCartDTO $request): void
    {
        if ($cart->products()->where('product_id', $product->id)->exists()) {
            $cart->products()->updateExistingPivot($product->id, ['count' => $request->productCount]);
        }
    }

    public function deleteProductFromCart(Cart $cart, Product $product): void
    {
        $cart->products()->detach($product->id);
    }

    public function getSummary(Cart $cart, ?WishList $wishList): array
    {
        $totalPrice = 0;
        foreach ($cart->products as $product) {
            $totalPrice += $product->pivot->price * $product->pivot->count;
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
            ],
            'has_free_delivery' => $hasFreeDelivery,
            'promo_code' => $cart->promoCode,
        ];
    }

    public function getProductsInCartWithSummary(Cart $cart, ?WishList $wishList): array
    {
        $summary = $this->getSummary($cart, $wishList);
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
