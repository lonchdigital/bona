@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="d-flex flex-wrap align-items-end justify-content-between mb-3">
                    <div>
                        <h2 class="mb-1 page-title">{{ trans('admin.search_results') }}</h2>
                        <p class="card-text mb-0">{{ trans('admin.search_results_description') }}</p>
                    </div>
                    <form class="form-inline mt-3 mt-md-0" method="get" action="{{ route('admin.search-query.list.page') }}">
                        <label class="sr-only" for="search-query-filter">{{ trans('admin.search_results_filter') }}</label>
                        <div class="input-group">
                            <input
                                id="search-query-filter"
                                class="form-control"
                                type="search"
                                name="query"
                                value="{{ $filter }}"
                                maxlength="160"
                                placeholder="{{ trans('admin.search_results_filter') }}"
                            >
                            <div class="input-group-append">
                                <button class="btn btn-dark" type="submit">{{ trans('admin.search') }}</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body py-3">
                        <div class="row text-center text-md-left">
                            <div class="col-md-4 py-2 border-md-right">
                                <small class="d-block text-muted text-uppercase">{{ trans('admin.search_results_unique') }}</small>
                                <strong class="h4 mb-0">{{ number_format($summary['total'], 0, ',', ' ') }}</strong>
                            </div>
                            <div class="col-md-4 py-2 border-md-right">
                                <small class="d-block text-muted text-uppercase">{{ trans('admin.search_results_total') }}</small>
                                <strong class="h4 mb-0">{{ number_format($summary['searches'], 0, ',', ' ') }}</strong>
                            </div>
                            <div class="col-md-4 py-2">
                                <small class="d-block text-muted text-uppercase">{{ trans('admin.search_results_empty') }}</small>
                                <strong class="h4 mb-0">{{ number_format($summary['no_results'], 0, ',', ' ') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow my-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>{{ trans('admin.search_results_query') }}</th>
                                    <th>{{ trans('admin.language') }}</th>
                                    <th>{{ trans('admin.search_results_count') }}</th>
                                    <th>{{ trans('admin.search_results_found') }}</th>
                                    <th>{{ trans('admin.search_results_first') }}</th>
                                    <th>{{ trans('admin.search_results_last') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($searchQueriesPaginated as $searchQuery)
                                    <tr>
                                        <td><strong>{{ $searchQuery->query }}</strong></td>
                                        <td><span class="badge badge-light text-uppercase">{{ $searchQuery->locale }}</span></td>
                                        <td>{{ number_format($searchQuery->search_count, 0, ',', ' ') }}</td>
                                        <td>
                                            @if($searchQuery->results_count > 0)
                                                {{ number_format($searchQuery->results_count, 0, ',', ' ') }}
                                            @else
                                                <span class="badge badge-warning">{{ trans('admin.search_results_nothing') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">{{ $searchQuery->first_searched_at?->format('d.m.Y H:i') }}</td>
                                        <td class="text-nowrap">{{ $searchQuery->last_searched_at?->format('d.m.Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">{{ trans('admin.search_results_no_data') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $searchQueriesPaginated->links('pagination.admin') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
