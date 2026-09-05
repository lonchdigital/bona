<?php

namespace App\Services\Search;

use App\Models\SearchQuery;
use App\Support\Search\SearchTerm;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SearchAnalyticsService
{
    public function record(string $query, int $resultsCount, ?string $locale = null): void
    {
        $normalized = SearchTerm::normalize($query);

        if (mb_strlen($normalized) < 3) {
            return;
        }

        $locale ??= app()->getLocale();
        $now = now();

        DB::table('search_queries')->insertOrIgnore([
            'query' => trim($query),
            'normalized_query' => $normalized,
            'locale' => $locale,
            'search_count' => 0,
            'results_count' => max(0, $resultsCount),
            'first_searched_at' => $now,
            'last_searched_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('search_queries')
            ->where('normalized_query', $normalized)
            ->where('locale', $locale)
            ->update([
                'query' => trim($query),
                'search_count' => DB::raw('search_count + 1'),
                'results_count' => max(0, $resultsCount),
                'last_searched_at' => $now,
                'updated_at' => $now,
            ]);
    }

    public function paginate(?string $filter = null): LengthAwarePaginator
    {
        return SearchQuery::query()
            ->when(filled($filter), function ($query) use ($filter) {
                $query->where('query', 'like', '%'.SearchTerm::normalize((string) $filter).'%');
            })
            ->orderByDesc('last_searched_at')
            ->paginate(config('domain.items_per_page'))
            ->withQueryString();
    }

    /** @return array{total: int, searches: int, no_results: int} */
    public function summary(): array
    {
        return [
            'total' => SearchQuery::count(),
            'searches' => (int) SearchQuery::sum('search_count'),
            'no_results' => SearchQuery::where('results_count', 0)->count(),
        ];
    }
}
