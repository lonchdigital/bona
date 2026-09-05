<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartProducts;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductGalleries;
use App\Models\User;
use App\Models\WishList;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Cart\DTO\AddPromoCodeToCartDTO;
use App\Services\Cart\DTO\ChangeProductCountInCartDTO;
use App\Services\Cart\DTO\DeleteProductFromCartDTO;
use App\Services\Cart\DTO\GetProductsSummaryWithDeliveryDTO;
use App\Services\Pricing\PricingService;
use App\Services\PromoCode\PromoCodeService;
use App\Services\WishList\WishListService;
use Illuminate\Support\Collection;

class CartService extends BaseService
{
    public function __construct(
        private readonly WishListService $wishListService,
        private readonly PricingService $pricingService,
        private readonly PromoCodeService $promoCodeService,
    ) {}

    public function getCartForGuestUser(string $token): ?Cart
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

    /**
     * Hands a guest's cart to the account that just signed in.
     *
     * If the account has no cart the guest's simply becomes theirs. If it has
     * one, the lines are carried over: a quantity for a product already there
     * is added to it rather than replacing it, since both were things the same
     * person meant to buy.
     */
    public function mergeGuestCartIntoUserCart(User $user, string $guestToken): void
    {
        $guestCart = $this->getCartForGuestUser($guestToken);

        if (! $guestCart) {
            return;
        }

        $this->coverWithDBTransactionWithoutResponse(function () use ($user, $guestCart) {
            $userCart = $this->getCartForAuthUser($user);

            if (! $userCart) {
                $guestCart->user_id = $user->id;
                $guestCart->token = null;
                $guestCart->save();

                return;
            }

            foreach ($guestCart->products as $product) {
                $existing = $userCart->products()->where('product_id', $product->id)->first();

                if ($existing) {
                    $userCart->products()->updateExistingPivot($product->id, [
                        'count' => $existing->pivot->count + $product->pivot->count,
                    ]);

                    continue;
                }

                $userCart->products()->attach($product->id, [
                    'count' => $product->pivot->count,
                    'price' => $product->pivot->price,
                    'attributes' => $product->pivot->attributes,
                    'attributes_price' => $product->pivot->attributes_price,
                ]);
            }

            // Keep the customer's existing campaign choice when both carts
            // have one; otherwise carry the valid code they applied before
            // signing in together with the guest cart lines.
            if (! $userCart->promo_code_id && $guestCart->promo_code_id) {
                $userCart->update(['promo_code_id' => $guestCart->promo_code_id]);
            }

            $guestCart->products()->detach();
            $guestCart->delete();
        });
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

        if (count($currentAttributeOptions)) {
            foreach ($currentAttributeOptions as $attribute) {
                $atr_options = [];
                foreach ($attribute->productAttributeOptions as $attributeOption) {
                    $atr_options[] = $attributeOption;
                }
                $attributeOptions[$attribute->id] = $atr_options;
            }
        }

        return $attributeOptions;
    }

    private function prepareRequestProductAttributes(array $requestProductAttributes): array
    {
        $dataToReturn = [];
        if (isset($requestProductAttributes['color_id'])) {
            $dataToReturn['color_name'] = $requestProductAttributes['color_id'];
            unset($requestProductAttributes['color_id']);
            unset($requestProductAttributes['color_name']);
        }

        foreach ($requestProductAttributes as $key => $attributeValue) {
            if (is_null($attributeValue)) {
                continue;
            }
            $dataToReturn[$key] = (string) json_decode($attributeValue, true)['id'];
        }

        return $dataToReturn;
    }

    public function addProductToCart(Cart $cart, Product $product, ChangeProductCountInCartDTO $request): void
    {
        $allProductVariations = CartProducts::where('cart_id', $cart->id)->where('product_id', $product->id)->get();
        $requestProductAttributes = $request->productAttributes;
        $isProductInCart = false;

        $requestProductAttributesAlt = (! is_null($requestProductAttributes)) ? $this->prepareRequestProductAttributes($requestProductAttributes) : null;

        foreach ($allProductVariations as $allProductVariation) {
            $isRequestedProduct = $this->isRequestedProduct($allProductVariation['attributes'], $requestProductAttributesAlt);

            if ($isRequestedProduct) {
                $count = $allProductVariation->count + $request->productCount;
                $allProductVariation->update(['count' => $count]);

                $isProductInCart = true;
                break;
            }
        }

        if (! $isProductInCart) {
            $attributeOptions = $this->getAttributesWithOptions($product->id, $product->productType);

            $productAttributesSum[] = 0;
            $productAttributeColor['color_id'] = null;

            if (! is_null($requestProductAttributes)) {

                if (isset($requestProductAttributes['color_id'])) {
                    $productAttributeColor['color_id'] = $requestProductAttributes['color_id'];
                    unset($requestProductAttributes['color_id']);
                    unset($requestProductAttributes['color_name']);
                }

                foreach ($requestProductAttributes as $attributeKey => $productAttr) {
                    if (! is_null($productAttr)) {
                        $productAtrID = preg_replace('/[^0-9]/', '', $attributeKey);
                        $attributeItself = json_decode($productAttr, true);

                        $productAttributesSum[] = collect($attributeOptions[$productAtrID])->firstWhere('id', $attributeItself['id'])->price;
                    }
                }

                if (! is_null($productAttributeColor['color_id'])) {
                    $color_price = $product->colors->firstWhere('id', $productAttributeColor['color_id'])->pivot->price;
                    if (is_numeric($color_price) || is_float($color_price)) {
                        $productAttributesSum[] = $color_price;
                    }
                }

            }

            $productAttributesSum = array_sum($productAttributesSum);

            /*$color = Color::where(function ($query) use ($productAttributeColor) {
                $query->whereJsonContains('name', ['uk' => $productAttributeColor['color']])
                    ->orWhereJsonContains('name', ['ru' => $productAttributeColor['color']]);
            })->first();*/

            $color = Color::where('id', $productAttributeColor['color_id'])->first();

            $currentImagePath = null;
            if ($color !== null) {
                $productGall = ProductGalleries::where('product_id', $product->id)->where('color_id', $color->id)->first();
                $currentImagePath = (! is_null($productGall)) ? $productGall->image_path : null;
            }

            $cart->products()->attach([$product->id => [
                'count' => $request->productCount,
                'price' => $product->price,
                'attributes' => json_encode($request->productAttributes),
                'attributes_price' => $productAttributesSum,
                'current_image_path' => $currentImagePath,
            ]]);
        }

    }

    public function addSubProductToCart(Cart $cart, Product $product, ChangeProductCountInCartDTO $request): void
    {
        if (! $cart->products()->where('product_id', $product->id)->exists()) {
            $cart->products()->attach([$product->id => ['count' => $request->productCount, 'price' => $product->price]]);
        } else {
            $productCount = $cart->products()->where('product_id', $product->id)->first()->pivot->count;
            $cart->products()->updateExistingPivot($product->id, ['count' => $productCount + $request->productCount]);
        }
    }

    public function changeProductCount(Cart $cart, Product $product, ChangeProductCountInCartDTO $request): void
    {
        if (! is_null($request->productAttributes)) { // all sub products have productAttributes as null

            $allProductVariations = CartProducts::where('cart_id', $cart->id)->where('product_id', $product->id)->get();
            $requestProductAttributes = $request->productAttributes;

            foreach ($allProductVariations as $allProductVariation) {
                $isRequestedProduct = $this->isRequestedProduct($allProductVariation['attributes'], $requestProductAttributes);

                //                dd('555', $allProductVariation['attributes'], $requestProductAttributes);

                if ($isRequestedProduct) {
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
        if (! is_null($request->productAttributes)) { // all sub products have productAttributes as null

            $allProductVariations = CartProducts::where('cart_id', $cart->id)->where('product_id', $product->id)->get();
            $requestProductAttributes = $request->productAttributes;

            foreach ($allProductVariations as $allProductVariation) {
                $isRequestedProduct = $this->isRequestedProduct($allProductVariation['attributes'], $requestProductAttributes);

                if ($isRequestedProduct) {
                    $allProductVariation->delete();
                    break;
                }
            }

        } else {
            $cart->products()->detach($product->id);
        }

    }

    private function isRequestedProduct($attributes, $requestProductAttributes): bool
    {
        $arr = json_decode((string) $attributes, true);

        /*
         * A line put in the cart without attributes — anything whose type has
         * none, an accessory say — stores null here. Reading a key straight
         * off the decoded null threw, so adding the same product a second time
         * answered 500 rather than raising its quantity.
         *
         * Such a line is the requested one only when the request carries no
         * attributes either.
         */
        if (! is_array($arr)) {
            return empty($requestProductAttributes);
        }

        if (empty($requestProductAttributes)) {
            return false;
        }

        $preparedArray = [];
        if (isset($arr['color_id']) && $arr['color_id'] !== '') {
            $preparedArray['color_name'] = (string) $arr['color_id'];
        }
        unset($arr['color_id']);
        unset($arr['color_name']);

        foreach ($arr as $key => $value) {
            if (is_null($value)) {
                continue;
            }
            $decoded = json_decode($value, true);

            if (! is_array($decoded) || ! isset($decoded['id'])) {
                continue;
            }

            $preparedArray[$key] = (string) $decoded['id'];
        }

        $normalizedRequestAttributes = collect($requestProductAttributes)
            ->filter(fn ($value) => $value !== null && (is_scalar($value) || $value instanceof \Stringable))
            ->mapWithKeys(fn ($value, $key) => [(string) $key => (string) $value])
            ->all();

        return $this->arraysAreEqual($preparedArray, $normalizedRequestAttributes);
    }

    public function getSummary(Cart $cart, ?WishList $wishList): array
    {
        $this->discardInvalidPromoCode($cart);

        $productsInWishList = $this->wishListService->getWishListProductsId($wishList);

        $cart->products->map(function ($product) use ($productsInWishList) {
            $product->is_in_wish_list = $productsInWishList->contains($product->id);
        });

        $totals = $this->pricingService->forCart($cart);

        return [
            'summary' => [
                'products' => $totals['products'],
                'total' => $totals['total'],
                'discount' => $totals['discount'],
            ],
            'has_free_delivery' => $totals['has_free_delivery'],
            'promo_code' => $cart->promoCode,
        ];
    }

    public function getCartSummary(Cart $cart): array
    {
        $this->discardInvalidPromoCode($cart);

        $totals = $this->pricingService->forCart($cart);

        return [
            'summary' => [
                'products' => $totals['products'],
                'total' => $totals['total'],
                'discount' => $totals['discount'],
            ],
            'has_free_delivery' => $totals['has_free_delivery'],
            'promo_code' => $cart->promoCode,
        ];
    }

    public function getProductsInCartWithSummary(Cart $cart, ?WishList $wishList): array
    {
        $summary = $this->getCartSummary($cart);
        //        $summary = $this->getSummary($cart, $wishList);

        $cart->loadMissing([
            'products.brand',
            'products.colors',
            'products.productType',
        ]);

        $cart->products->each(function ($product) {
            $product->name = $product->getRawOriginal('name');
        });

        $summary['products'] = $cart->products;

        /*$response = new \Illuminate\Http\Response(json_encode($summary));
        $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', '0');*/

        return $summary;
    }

    public function attachPromoCode(AddPromoCodeToCartDTO $request, Cart $cart): ServiceActionResult
    {
        $code = $this->promoCodeService->findByCode($request->code);

        if (! $code) {
            return ServiceActionResult::make(false, trans('base.promo_code_invalid'));
        }

        $result = $this->promoCodeService->validateForCart($code, $cart);
        if (! $result->isSuccess()) {
            return $result;
        }

        $cart->update([
            'promo_code_id' => $code->id,
        ]);

        return ServiceActionResult::make(true, trans('base.promo_code_add_success'));
    }

    public function detachPromoCode(Cart $cart): void
    {
        $cart->update(['promo_code_id' => null]);
        $cart->unsetRelation('promoCode');
    }

    private function discardInvalidPromoCode(Cart $cart): void
    {
        if (! $cart->exists || ! $cart->promo_code_id) {
            return;
        }

        $promoCode = $cart->promoCode;
        if ($promoCode && $this->promoCodeService->validateForCart($promoCode, $cart)->isSuccess()) {
            return;
        }

        $this->detachPromoCode($cart);
    }

    public function getCartSummaryWithDelivery(GetProductsSummaryWithDeliveryDTO $request, Cart $cart, ?WishList $wishList): array
    {
        $totals = $this->pricingService->forCart($cart, $request->deliveryTypeId);

        return [
            'summary' => $totals,
            'has_free_delivery' => $totals['has_free_delivery'],
            'promo_code' => $cart->promoCode,
        ];
    }
}
