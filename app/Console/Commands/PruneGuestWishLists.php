<?php

namespace App\Console\Commands;

use App\Services\WishList\WishListService;
use Illuminate\Console\Command;

/**
 * Clears out wish lists left behind by visitors who never signed in.
 *
 * A guest is remembered by a cookie that lasts thirty days, so once that
 * cookie is gone nobody can reach the row it pointed at — it would sit in the
 * table for good. Lists belonging to an account are never touched: those are
 * the visitor's on purpose and stay until they empty them.
 */
class PruneGuestWishLists extends Command
{
    protected $signature = 'wishlist:prune-guests {--days= : Override the retention period, in days}';

    protected $description = 'Delete guest wish lists that have not been touched for the retention period';

    public function handle(WishListService $wishListService): int
    {
        $pruned = $wishListService->pruneStaleGuestWishLists(
            $this->option('days') !== null ? (int) $this->option('days') : null
        );

        $this->info($pruned === 0 ? 'Nothing to prune.' : "Pruned {$pruned} guest wish list(s).");

        return self::SUCCESS;
    }
}
