@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap: 12px">
                    <div>
                        <h2 class="mb-2 page-title">{{ trans('admin.customer_reviews') }}</h2>
                        <p class="text-muted mb-0">{{ trans('admin.customer_reviews_hint') }}</p>
                    </div>
                    <a href="{{ route('admin.customer-review.create.page') }}" class="btn btn-dark">
                        {{ trans('admin.customer_review_add') }}
                    </a>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <a href="{{ route('admin.customer-review.list.page') }}"
                           class="btn btn-sm {{ !$selectedStatusId ? 'btn-dark' : 'btn-outline-dark' }}">
                            {{ trans('admin.all') }}
                        </a>
                        @foreach(\App\DataClasses\ProductReviewStatusesDataClass::get() as $status)
                            <a href="{{ route('admin.customer-review.list.page', ['status' => $status['id']]) }}"
                               class="btn btn-sm {{ $selectedStatusId === $status['id'] ? 'btn-dark' : 'btn-outline-dark' }}">
                                {{ $status['name'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="row my-4">
                    <div class="col-md-12">
                        <div class="card shadow">
                            <div class="card-body">
                                @if(Session::has('success'))
                                    <div class="alert alert-success" role="alert">{{ Session::get('success') }}</div>
                                @endif
                                @if(Session::has('error'))
                                    <div class="alert alert-danger" role="alert">{{ Session::get('error') }}</div>
                                @endif

                                @if(!count($reviewsPaginated))
                                    <p class="mb-0">{{ trans('admin.customer_reviews_empty') }}</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ trans('admin.name') }}</th>
                                                <th>{{ trans('admin.customer_review_source') }}</th>
                                                <th>{{ trans('base.product_review_rating') }}</th>
                                                <th>{{ trans('base.product_review_text') }}</th>
                                                <th>{{ trans('admin.status') }}</th>
                                                <th>{{ trans('admin.created_at') }}</th>
                                                <th class="text-right">{{ trans('admin.action') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($reviewsPaginated as $review)
                                                @php($status = \App\DataClasses\ProductReviewStatusesDataClass::get($review->status_id))
                                                <tr>
                                                    <td>{{ $review->id }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center" style="gap: 10px;">
                                                            @if($review->author_avatar_url)
                                                                <img src="{{ $review->author_avatar_url }}" alt="" width="36" height="36" loading="lazy" referrerpolicy="no-referrer" style="border-radius: 50%; object-fit: cover;">
                                                            @endif
                                                            <span>{{ $review->author_name }}</span>
                                                        </div>
                                                        @if($review->phone)
                                                            <br><small class="text-muted">{{ $review->phone }}</small>
                                                        @endif
                                                        @if($review->email)
                                                            <br><small class="text-muted">{{ $review->email }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($review->source_url)
                                                            <a href="{{ $review->source_url }}" target="_blank" rel="noopener noreferrer">
                                                                {{ trans('admin.customer_review_source_'.$review->source) }} ↗
                                                            </a>
                                                        @else
                                                            {{ trans('admin.customer_review_source_'.$review->source) }}
                                                        @endif
                                                        @if($review->reviewed_at)
                                                            <br><small class="text-muted">{{ $review->reviewed_at->format('d-m-Y') }}</small>
                                                        @elseif($review->source_published_label)
                                                            <br><small class="text-muted">{{ $review->source_published_label }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $review->rating }}/5</td>
                                                    <td style="min-width: 260px; max-width: 420px; white-space: normal;">{{ $review->review }}</td>
                                                    <td>
                                                        <span class="badge" style="background-color: {{ $status['color'] ?? '#eee' }}">
                                                            {{ $status['name'] ?? '—' }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $review->created_at->format('d-m-Y H:i') }}</td>
                                                    <td class="text-right" style="white-space: nowrap;">
                                                        <a href="{{ route('admin.customer-review.edit.page', ['customerReview' => $review->id]) }}" class="btn btn-sm btn-primary">
                                                            {{ trans('admin.edit') }}
                                                        </a>
                                                        @if($review->status_id !== \App\DataClasses\ProductReviewStatusesDataClass::STATUS_APPROVED)
                                                            <form action="{{ route('admin.customer-review.approve', ['customerReview' => $review->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success">{{ trans('admin.product_review_approve') }}</button>
                                                            </form>
                                                        @endif
                                                        @if($review->status_id !== \App\DataClasses\ProductReviewStatusesDataClass::STATUS_REJECTED)
                                                            <form action="{{ route('admin.customer-review.reject', ['customerReview' => $review->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-secondary">{{ trans('admin.product_review_reject') }}</button>
                                                            </form>
                                                        @endif
                                                        <form action="{{ route('admin.customer-review.delete', ['customerReview' => $review->id]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger">{{ trans('admin.delete') }}</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    {{ $reviewsPaginated->links('pagination.admin') }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
