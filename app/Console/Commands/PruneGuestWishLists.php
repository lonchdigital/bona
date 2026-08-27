<?php

namespace App\Console\Commands;

use App\Models\WishList;
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
    protected $signature = 'wishlist:prune-guests {--days=30 : How long an untouched guest list is kept}';

    protected $description = 'Delete guest wish lists that have not been touched for the retention period';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));

        $stale = WishList::query()
            ->whereNull('owner_id')
            ->where('updated_at', '<', now()->subDays($days));

        $count = (clone $stale)->count();

        if ($count === 0) {
            $this->info('Nothing to prune.');

            return self::SUCCESS;
        }

        // Chunked so a long-neglected table doesn't load in one go, and
        // deleted one by one so the pivot rows go with them.
        $stale->chunkById(200, function ($wishLists) {
            foreach ($wishLists as $wishList) {
                $wishList->products()->detach();
                $wishList->delete();
            }
        });

        $this->info("Pruned {$count} guest wish list(s) untouched for {$days} days.");

        return self::SUCCESS;
    }
}
