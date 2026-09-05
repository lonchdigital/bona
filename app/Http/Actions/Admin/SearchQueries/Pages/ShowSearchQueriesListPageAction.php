<?php

namespace App\Http\Actions\Admin\SearchQueries\Pages;

use App\Services\Search\SearchAnalyticsService;
use Illuminate\Http\Request;

class ShowSearchQueriesListPageAction
{
    public function __invoke(Request $request, SearchAnalyticsService $analyticsService)
    {
        $validated = $request->validate([
            'query' => ['nullable', 'string', 'max:160'],
        ]);

        return view('pages.admin.search-queries.list', [
            'searchQueriesPaginated' => $analyticsService->paginate($validated['query'] ?? null),
            'summary' => $analyticsService->summary(),
            'filter' => $validated['query'] ?? '',
        ]);
    }
}
