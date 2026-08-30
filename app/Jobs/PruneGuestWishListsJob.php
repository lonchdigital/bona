<?php

namespace App\Jobs;

use App\Services\WishList\WishListService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Clears out guest wish lists whose cookie has run out.
 *
 * Dispatched from ordinary traffic rather than left to the scheduler: this
 * host gives the site's account no cron of its own, so a scheduled command
 * would simply never fire. The same after-response dispatch the sitemap uses.
 */
class PruneGuestWishListsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(WishListService $wishListService): void
    {
        $pruned = $wishListService->pruneStaleGuestWishLists();

        if ($pruned > 0) {
            \Log::info("Pruned {$pruned} stale guest wish list(s).");
        }
    }
}
