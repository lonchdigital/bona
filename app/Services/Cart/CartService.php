<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartProducts;
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
use App\Support\Commerce\ProductBundle;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

            $bundleKeyMap = [];

            foreach ($guestCart->products as $product) {
                $guestBundleKey = trim((string) ($product->pivot->bundle_key ?? ''));

                if ($guestBundleKey !== '') {
                    $bundleKeyMap[$guestBundleKey] ??= CartProducts::query()
                        ->where('cart_id', $userCart->id)
                        ->where('bundle_key', $guestBundleKey)
                        ->exists()
                            ? (string) Str::uuid()
                            : $guestBundleKey;

                    CartProducts::create([
                        'cart_id' => $userCart->id,
                        'product_id' => $product->id,
                        'count' => $product->pivot->count,
                        'price' => $product->pivot->price,
                        'attributes' => $product->pivot->attributes,
                        'attributes_price' => $product->pivot->attributes_price,
                        'current_image_path' => $product->pivot->current_image_path,
                        'bundle_key' => $bundleKeyMap[$guestBundleKey],
                        'bundle_role' => $product->pivot->bundle_role,
                        'bundle_category' => $product->pivot->bundle_category,
                    ]);

                    continue;
                }

                $existingQuery = CartProducts::query()
                    ->where('cart_id', $userCart->id)
                    ->where('product_id', $product->id)
                    ->whereNull('bundle_key');

                $existing = is_null($product->pivot->attributes)
                    ? $existingQuery->whereNull('attributes')->first()
                    : $existingQuery->where('attributes', $product->pivot->attributes)->first();

                if ($existing) {
                    $existing->update(['count' => $existing->count + $product->pivot->count]);

                    continue;
                }

                CartProducts::create([
                    'cart_id' => $userCart->id,
                    'product_id' => $product->id,
                    'count' => $product->pivot->count,
                    'price' => $product->pivot->price,
                    'attributes' => $product->pivot->attributes,
                    'attributes_price' => $product->pivot->attributes_price,
                    'current_image_path' => $product->pivot->current_image_path,
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
        $requestProductAttributes = $request->productAttributes;
        $isProductInCart = false;

        if ($request->bundleKey !== null && CartProducts::query()
            ->where('cart_id', $cart->id)
            ->where('bundle_key', $request->bundleKey)
            ->exists()) {
            throw ValidationException::withMessages([
                'bundle_key' => trans('base.cart_bundle_invalid'),
            ]);
        }

        $allProductVariations = CartProducts::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->whereNull('bundle_key')
            ->get();
        $requestProductAttributesAlt = (! is_null($requestProductAttributes))
            ? $this->prepareRequestProductAttributes($requestProductAttributes)
            : null;

        if ($request->bundleKey === null) {
            foreach ($allProductVariations as $allProductVariation) {
                $isRequestedProduct = $this->isRequestedProduct($allProductVariation['attributes'], $requestProductAttributesAlt);

                if ($isRequestedProduct) {
                    $count = $allProductVariation->count + $request->productCount;
                    $allProductVariation->update(['count' => $count]);

                    $isProductInCart = true;
                    break;
                }
            }
        }

        if (! $isProductInCart) {
            $line = $this->prepareProductLine($product, $requestProductAttributes);

            CartProducts::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'count' => $request->productCount,
                'price' => $line['price'],
                'attributes' => $line['attributes'],
                'attributes_price' => $line['attributes_price'],
                'current_image_path' => $line['current_image_path'],
                'bundle_key' => $request->bundleKey,
                'bundle_role' => $request->bundleKey ? ProductBundle::ROLE_PARENT : null,
                'bundle_category' => null,
            ]);
        }
    }

    public function addSubProductToCart(Cart $cart, Product $product, ChangeProductCountInCartDTO $request): void
    {
        if ($request->bundleKey !== null) {
            $parentLine = CartProducts::query()
                ->where('cart_id', $cart->id)
                ->where('bundle_key', $request->bundleKey)
                ->where('bundle_role', ProductBundle::ROLE_PARENT)
                ->first();

            $parentProduct = $parentLine ? Product::find($parentLine->product_id) : null;
            $allowedSubProducts = collect(json_decode((string) $parentProduct?->sub_products, true))
                ->map(fn ($id) => (int) $id);

            if (! $parentLine || ! $allowedSubProducts->contains((int) $product->id)) {
                throw ValidationException::withMessages([
                    'bundle_key' => trans('base.cart_bundle_invalid'),
                ]);
            }

            $existing = CartProducts::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->where('bundle_key', $request->bundleKey)
                ->where('bundle_role', ProductBundle::ROLE_ITEM)
                ->first();

            if ($existing) {
                $existing->update(['count' => $existing->count + $request->productCount]);

                return;
            }

            $product->loadMissing('categories');
            $category = $product->categories->first();
            $categoryName = $category?->getRawOriginal('name');
            if (is_array($categoryName)) {
                $categoryName = json_encode($categoryName, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            }
            $categoryName = $categoryName ?: json_encode([
                'uk' => trans('base.cart_bundle_item', [], 'uk'),
                'ru' => trans('base.cart_bundle_item', [], 'ru'),
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

            CartProducts::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'count' => $request->productCount,
                'price' => $product->price,
                'attributes' => null,
                'attributes_price' => 0,
                'current_image_path' => null,
                'bundle_key' => $request->bundleKey,
                'bundle_role' => ProductBundle::ROLE_ITEM,
                'bundle_category' => $categoryName,
            ]);

            return;
        }

        $existing = CartProducts::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->whereNull('bundle_key')
            ->first();

        if ($existing) {
            $existing->update(['count' => $existing->count + $request->productCount]);

            return;
        }

        CartProducts::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'count' => $request->productCount,
            'price' => $product->price,
        ]);
    }

    public function changeProductCount(Cart $cart, Product $product, ChangeProductCountInCartDTO $request): void
    {
        if ($request->cartLineId !== null) {
            CartProducts::query()
                ->whereKey($request->cartLineId)
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first()
                ?->update(['count' => $request->productCount]);

            return;
        }

        if (! is_null($request->productAttributes)) { // all sub products have productAttributes as null

            $allProductVariations = CartProducts::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->whereNull('bundle_key')
                ->get();
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

            CartProducts::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->whereNull('bundle_key')
                ->first()
                ?->update(['count' => $request->productCount]);

        }

    }

    public function deleteProductFromCart(Cart $cart, Product $product, DeleteProductFromCartDTO $request): void
    {
        if ($request->cartLineId !== null) {
            $line = CartProducts::query()
                ->whereKey($request->cartLineId)
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if (! $line) {
                return;
            }

            if ($line->bundle_role === ProductBundle::ROLE_PARENT && $line->bundle_key) {
                CartProducts::query()
                    ->where('cart_id', $cart->id)
                    ->where('bundle_key', $line->bundle_key)
                    ->delete();
            } else {
                $line->delete();
            }

            return;
        }

        if (! is_null($request->productAttributes)) { // all sub products have productAttributes as null

            $allProductVariations = CartProducts::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->whereNull('bundle_key')
                ->get();
            $requestProductAttributes = $request->productAttributes;

            foreach ($allProductVariations as $allProductVariation) {
                $isRequestedProduct = $this->isRequestedProduct($allProductVariation['attributes'], $requestProductAttributes);

                if ($isRequestedProduct) {
                    $allProductVariation->delete();
                    break;
                }
            }

        } else {
            CartProducts::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->whereNull('bundle_key')
                ->delete();
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

    /** @return array{price: float|int|string|null, attributes: ?string, attributes_price: float, current_image_path: ?string} */
    private function prepareProductLine(Product $product, ?array $requestProductAttributes): array
    {
        $attributeOptions = $this->getAttributesWithOptions($product->id, $product->productType);
        $attributesForPricing = $requestProductAttributes ?? [];
        $attributesPrice = 0.0;
        $colorId = $attributesForPricing['color_id'] ?? null;
        unset($attributesForPricing['color_id'], $attributesForPricing['color_name']);

        foreach ($attributesForPricing as $attributeKey => $productAttribute) {
            if ($productAttribute === null) {
                continue;
            }

            $attributeId = (int) preg_replace('/[^0-9]/', '', (string) $attributeKey);
            $selectedOption = is_string($productAttribute) ? json_decode($productAttribute, true) : $productAttribute;
            if (! is_array($selectedOption) || ! isset($selectedOption['id'])) {
                continue;
            }

            $option = collect($attributeOptions[$attributeId] ?? [])->firstWhere('id', $selectedOption['id']);
            $attributesPrice += (float) ($option?->price ?? 0);
        }

        $color = $colorId !== null ? $product->colors->firstWhere('id', (int) $colorId) : null;
        $attributesPrice += (float) ($color?->pivot?->price ?? 0);
        $gallery = $color
            ? ProductGalleries::query()->where('product_id', $product->id)->where('color_id', $color->id)->first()
            : null;

        return [
            'price' => $product->price,
            'attributes' => $requestProductAttributes === null
                ? null
                : json_encode($requestProductAttributes, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'attributes_price' => $attributesPrice,
            'current_image_path' => $gallery?->image_path,
        ];
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
            'products.productType.attributes',
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
