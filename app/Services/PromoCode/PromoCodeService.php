<?php

namespace App\Services\PromoCode;

use App\Models\Cart;
use App\Models\Product;
use App\Models\PromoCode;
use App\Services\Base\ServiceActionResult;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PromoCodeService
{
    public function paginate()
    {
        return PromoCode::query()
            ->withCount(['products', 'brands', 'categories', 'productTypes', 'orders'])
            ->latest('id')
            ->paginate(config('domain.items_per_page'));
    }

    public function create(array $data): PromoCode
    {
        return DB::transaction(function () use ($data) {
            $targets = $this->extractTargets($data);

            $promoCode = PromoCode::create($this->normalizePersistenceData($data));
            $this->syncTargets($promoCode, $targets);

            return $promoCode;
        });
    }

    public function update(PromoCode $promoCode, array $data): PromoCode
    {
        return DB::transaction(function () use ($promoCode, $data) {
            $targets = $this->extractTargets($data);

            $promoCode->update($this->normalizePersistenceData($data, (int) $promoCode->usage_count));
            $this->syncTargets($promoCode, $targets);

            return $promoCode->refresh();
        });
    }

    public function deleteOrDeactivate(PromoCode $promoCode): bool
    {
        return DB::transaction(function () use ($promoCode) {
            if ($promoCode->orders()->exists()) {
                $promoCode->update(['is_active' => false]);

                return false;
            }

            DB::table('carts')->where('promo_code_id', $promoCode->id)->update(['promo_code_id' => null]);
            $promoCode->delete();

            return true;
        });
    }

    public function findByCode(string $code): ?PromoCode
    {
        return PromoCode::query()
            ->whereRaw('UPPER(code) = ?', [Str::upper(trim($code))])
            ->first();
    }

    public function validateForCart(PromoCode $promoCode, Cart $cart): ServiceActionResult
    {
        if (! $promoCode->is_active) {
            return ServiceActionResult::make(false, trans('base.promo_code_inactive'));
        }

        if ($promoCode->starts_at && now()->lt($promoCode->starts_at)) {
            return ServiceActionResult::make(false, trans('base.promo_code_not_started'));
        }

        if ($promoCode->expires_at && now()->gt($promoCode->expires_at)) {
            return ServiceActionResult::make(false, trans('base.promo_code_expired'));
        }

        $usageLimit = $promoCode->effectiveUsageLimit();
        if (($usageLimit !== null && $promoCode->usage_count >= $usageLimit) || $promoCode->is_used) {
            return ServiceActionResult::make(false, trans('base.promo_code_already_used'));
        }

        if (! $cart->exists || ! $cart->products()->exists()) {
            return ServiceActionResult::make(false, trans('base.promo_code_empty_cart'));
        }

        $subtotal = $this->subtotal($cart->products);
        if ($subtotal < (float) $promoCode->minimum_order_amount) {
            return ServiceActionResult::make(false, trans('base.promo_code_minimum_order', [
                'amount' => $this->money((float) $promoCode->minimum_order_amount),
            ]));
        }

        if ($this->eligibleSubtotal($promoCode, $cart->products) <= 0) {
            return ServiceActionResult::make(false, trans('base.promo_code_not_applicable'));
        }

        return ServiceActionResult::make(true, trans('base.promo_code_add_success'));
    }

    public function discount(PromoCode $promoCode, Enumerable $products): float
    {
        $subtotal = $this->subtotal($products);
        if ($subtotal < (float) $promoCode->minimum_order_amount) {
            return 0;
        }

        $eligibleSubtotal = $this->eligibleSubtotal($promoCode, $products);
        if ($eligibleSubtotal <= 0) {
            return 0;
        }

        $discount = $promoCode->discount_type === PromoCode::TYPE_FIXED
            ? min($promoCode->effectiveDiscountValue(), $eligibleSubtotal)
            : $eligibleSubtotal * min(100, max(0, $promoCode->effectiveDiscountValue())) / 100;

        if ($promoCode->maximum_discount_amount !== null) {
            $discount = min($discount, (float) $promoCode->maximum_discount_amount);
        }

        return round(min($discount, $subtotal), 2);
    }

    public function label(PromoCode $promoCode): string
    {
        if ($promoCode->discount_type === PromoCode::TYPE_FIXED) {
            return '−'.$this->money($promoCode->effectiveDiscountValue());
        }

        return '−'.rtrim(rtrim(number_format($promoCode->effectiveDiscountValue(), 2, '.', ''), '0'), '.').'%';
    }

    private function eligibleSubtotal(PromoCode $promoCode, Enumerable $products): float
    {
        $eligibleProductIds = $this->eligibleProductIds($promoCode, $products);
        $remainingItems = $promoCode->max_discounted_items;
        $subtotal = 0;

        foreach ($products as $product) {
            if ($eligibleProductIds !== null && ! in_array((int) $product->id, $eligibleProductIds, true)) {
                continue;
            }

            $quantity = max(0, (int) $product->pivot->count);
            if ($remainingItems !== null) {
                $quantity = min($quantity, max(0, $remainingItems));
                $remainingItems -= $quantity;
            }

            if ($quantity <= 0) {
                break;
            }

            $subtotal += $this->unitPrice($product) * $quantity;
        }

        return round($subtotal, 2);
    }

    /** @return array<int, int>|null */
    private function eligibleProductIds(PromoCode $promoCode, Enumerable $products): ?array
    {
        if ($promoCode->all_products) {
            return null;
        }

        $promoCode->loadMissing(['products:id', 'brands:id', 'categories:id', 'productTypes:id']);

        $directProductIds = $promoCode->products->pluck('id')->map(fn ($id) => (int) $id)->all();
        $brandIds = $promoCode->brands->pluck('id')->map(fn ($id) => (int) $id)->all();
        $categoryIds = $promoCode->categories->pluck('id')->map(fn ($id) => (int) $id)->all();
        $productTypeIds = $promoCode->productTypes->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($directProductIds === [] && $brandIds === [] && $categoryIds === [] && $productTypeIds === []) {
            return [];
        }

        return Product::query()
            ->whereIn('id', $products->pluck('id')->map(fn ($id) => (int) $id)->all())
            ->where(function ($query) use ($directProductIds, $brandIds, $categoryIds, $productTypeIds) {
                if ($directProductIds !== []) {
                    $query->orWhereIn('id', $directProductIds);
                }

                if ($brandIds !== []) {
                    $query->orWhereIn('brand_id', $brandIds);
                }

                if ($productTypeIds !== []) {
                    $query
                        ->orWhereIn('product_type_id', $productTypeIds)
                        ->orWhereHas('productTypes', fn ($relation) => $relation->whereIn('product_types.id', $productTypeIds));
                }

                if ($categoryIds !== []) {
                    $query->orWhereHas('categories', fn ($relation) => $relation->whereIn('categories.id', $categoryIds));
                }
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function subtotal(Enumerable $products): float
    {
        return round($products->sum(fn ($product) => $this->unitPrice($product) * max(0, (int) $product->pivot->count)), 2);
    }

    private function unitPrice($product): float
    {
        return (float) $product->pivot->price + (float) ($product->pivot->attributes_price ?? 0);
    }

    private function money(float $amount): string
    {
        return number_format($amount, 0, ',', ' ').' '.trans('base.uah');
    }

    private function normalizePersistenceData(array $data, int $usageCount = 0): array
    {
        $data['code'] = Str::upper(trim($data['code']));
        $data['discount'] = $data['discount_type'] === PromoCode::TYPE_PERCENT
            ? (int) round((float) $data['discount_value'])
            : 0;
        $data['is_used'] = $data['usage_limit'] !== null
            && $usageCount >= (int) $data['usage_limit'];

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{products: array<int, int>, brands: array<int, int>, categories: array<int, int>, product_types: array<int, int>}
     */
    private function extractTargets(array &$data): array
    {
        $allProducts = (bool) ($data['all_products'] ?? false);
        $targets = [
            'products' => $allProducts ? [] : array_map('intval', $data['product_ids'] ?? []),
            'brands' => $allProducts ? [] : array_map('intval', $data['brand_ids'] ?? []),
            'categories' => $allProducts ? [] : array_map('intval', $data['category_ids'] ?? []),
            'product_types' => $allProducts ? [] : array_map('intval', $data['product_type_ids'] ?? []),
        ];

        unset($data['product_ids'], $data['brand_ids'], $data['category_ids'], $data['product_type_ids']);

        return $targets;
    }

    /** @param array{products: array<int, int>, brands: array<int, int>, categories: array<int, int>, product_types: array<int, int>} $targets */
    private function syncTargets(PromoCode $promoCode, array $targets): void
    {
        $promoCode->products()->sync($targets['products']);
        $promoCode->brands()->sync($targets['brands']);
        $promoCode->categories()->sync($targets['categories']);
        $promoCode->productTypes()->sync($targets['product_types']);
    }
}
