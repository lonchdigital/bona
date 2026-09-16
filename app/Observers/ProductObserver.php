<?php

namespace App\Observers;

use App\Jobs\RegenerateSitemapJob;
use App\Jobs\UpdateCountOfProductsByCategoryJob;
use App\Models\Product;
use App\Models\ProductSlugRedirect;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        UpdateCountOfProductsByCategoryJob::dispatchAfterResponse($product->id);
        RegenerateSitemapJob::dispatchAfterResponse();
    }

    /**
     * Keep the previous address working after a manager edits the slug.
     */
    public function updating(Product $product): void
    {
        $previous = (string) $product->getOriginal('slug');

        if (! $product->isDirty('slug') || $previous === '' || $previous === $product->slug) {
            return;
        }

        ProductSlugRedirect::query()->where('slug', $product->slug)->delete();
        ProductSlugRedirect::query()->updateOrCreate(
            ['slug' => $previous],
            ['product_id' => $product->id, 'target_path' => null],
        );
    }

    /**
     * A removed product sends its visitors, and every address that used to
     * lead to it, to its catalog instead of a 404.
     */
    public function deleting(Product $product): void
    {
        $catalogPath = $product->productType?->slug
            ? '/product-category/'.$product->productType->slug
            : null;

        if ($catalogPath === null) {
            return;
        }

        ProductSlugRedirect::query()
            ->where('product_id', $product->id)
            ->update(['product_id' => null, 'target_path' => $catalogPath]);

        ProductSlugRedirect::query()->updateOrCreate(
            ['slug' => $product->slug],
            ['product_id' => null, 'target_path' => $catalogPath],
        );
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        UpdateCountOfProductsByCategoryJob::dispatchAfterResponse($product->id);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        UpdateCountOfProductsByCategoryJob::dispatchAfterResponse($product->id);
        RegenerateSitemapJob::dispatchAfterResponse();
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
