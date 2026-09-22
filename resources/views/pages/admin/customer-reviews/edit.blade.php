@extends('layouts.admin-main')

@php
    $review = $review ?? null;
    $isEditing = (bool) $review;
    $nameTranslations = old('author_name_translations', $review?->author_name_translations ?? []);
    $reviewTranslations = old('review_translations', $review?->review_translations ?? []);
@endphp

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">
                    {{ trans($isEditing ? 'admin.customer_review_edit' : 'admin.customer_review_new') }}
                </h2>

                <div class="card shadow mb-4">
                    <div class="card-header">
                        <strong class="card-title">{{ trans('admin.customer_review_information') }}</strong>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            {{ trans('admin.customer_review_google_help') }}
                        </div>

                        <form method="POST" action="{{ $isEditing
                            ? route('admin.customer-review.edit', ['customerReview' => $review->id])
                            : route('admin.customer-review.create') }}">
                            @csrf

                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label for="source">{{ trans('admin.customer_review_source') }} <strong class="text-danger">*</strong></label>
                                    <select class="form-control @error('source') is-invalid @enderror" id="source" name="source" required>
                                        @foreach([
                                            \App\Models\CustomerReview::SOURCE_GOOGLE,
                                            \App\Models\CustomerReview::SOURCE_WEBSITE,
                                            \App\Models\CustomerReview::SOURCE_MANUAL,
                                        ] as $source)
                                            <option value="{{ $source }}" @selected(old('source', $review?->source ?? \App\Models\CustomerReview::SOURCE_GOOGLE) === $source)>
                                                {{ trans('admin.customer_review_source_'.$source) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4 form-group">
                                    <label for="status_id">{{ trans('admin.status') }} <strong class="text-danger">*</strong></label>
                                    <select class="form-control @error('status_id') is-invalid @enderror" id="status_id" name="status_id" required>
                                        @foreach(\App\DataClasses\ProductReviewStatusesDataClass::get() as $status)
                                            <option value="{{ $status['id'] }}" @selected((int) old('status_id', $review?->status_id ?? \App\DataClasses\ProductReviewStatusesDataClass::STATUS_APPROVED) === $status['id'])>
                                                {{ $status['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4 form-group">
                                    <label for="locale">{{ trans('admin.customer_review_original_language') }}</label>
                                    <select class="form-control @error('locale') is-invalid @enderror" id="locale" name="locale">
                                        <option value="uk" @selected(old('locale', $review?->locale ?? 'uk') === 'uk')>Українська</option>
                                        <option value="ru" @selected(old('locale', $review?->locale ?? 'uk') === 'ru')>Русский</option>
                                    </select>
                                    @error('locale')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="source_url">{{ trans('admin.customer_review_source_url') }}</label>
                                <input class="form-control @error('source_url') is-invalid @enderror" id="source_url" name="source_url" type="url" maxlength="2048" value="{{ old('source_url', $review?->source_url) }}" placeholder="https://g.co/kgs/…">
                                @error('source_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group">
                                <label for="author_avatar_url">{{ trans('admin.customer_review_avatar_url') }}</label>
                                <input class="form-control @error('author_avatar_url') is-invalid @enderror" id="author_avatar_url" name="author_avatar_url" type="url" maxlength="2048" value="{{ old('author_avatar_url', $review?->author_avatar_url) }}" placeholder="https://lh3.googleusercontent.com/…">
                                @error('author_avatar_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="row">
                                <div class="col-md-8 form-group">
                                    <label for="author_name">{{ trans('admin.customer_review_author_original') }} <strong class="text-danger">*</strong></label>
                                    <input class="form-control @error('author_name') is-invalid @enderror" id="author_name" name="author_name" type="text" maxlength="121" required value="{{ old('author_name', $review?->author_name) }}">
                                    @error('author_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-2 form-group">
                                    <label for="rating">{{ trans('admin.rating') }} <strong class="text-danger">*</strong></label>
                                    <select class="form-control @error('rating') is-invalid @enderror" id="rating" name="rating" required>
                                        @foreach(range(5, 1) as $rating)
                                            <option value="{{ $rating }}" @selected((int) old('rating', $review?->rating ?? 5) === $rating)>{{ $rating }} / 5</option>
                                        @endforeach
                                    </select>
                                    @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-2 form-group">
                                    <label for="reviewed_at">{{ trans('admin.customer_review_date') }}</label>
                                    <input class="form-control @error('reviewed_at') is-invalid @enderror" id="reviewed_at" name="reviewed_at" type="date" value="{{ old('reviewed_at', $review?->reviewed_at?->toDateString()) }}">
                                    @error('reviewed_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="source_published_label">{{ trans('admin.customer_review_source_date_label') }}</label>
                                <input class="form-control @error('source_published_label') is-invalid @enderror" id="source_published_label" name="source_published_label" type="text" maxlength="80" value="{{ old('source_published_label', $review?->source_published_label) }}" placeholder="2 тижні тому">
                                @error('source_published_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group">
                                <label for="review">{{ trans('admin.customer_review_text_original') }} <strong class="text-danger">*</strong></label>
                                <textarea class="form-control @error('review') is-invalid @enderror" id="review" name="review" rows="6" maxlength="5000" required>{{ old('review', $review?->review) }}</textarea>
                                @error('review')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <h5 class="mt-4">{{ trans('admin.customer_review_translations') }}</h5>
                            <p class="text-muted">{{ trans('admin.customer_review_translations_help') }}</p>
                            <div class="row">
                                @foreach(['uk' => 'Українська', 'ru' => 'Русский'] as $language => $label)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="author_name_{{ $language }}">{{ trans('admin.name') }} — {{ $label }}</label>
                                            <input class="form-control @error('author_name_translations.'.$language) is-invalid @enderror" id="author_name_{{ $language }}" name="author_name_translations[{{ $language }}]" type="text" maxlength="121" value="{{ $nameTranslations[$language] ?? '' }}">
                                            @error('author_name_translations.'.$language)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="review_{{ $language }}">{{ trans('base.product_review_text') }} — {{ $label }}</label>
                                            <textarea class="form-control @error('review_translations.'.$language) is-invalid @enderror" id="review_{{ $language }}" name="review_translations[{{ $language }}]" rows="4" maxlength="5000">{{ $reviewTranslations[$language] ?? '' }}</textarea>
                                            @error('review_translations.'.$language)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <h5 class="mt-3">{{ trans('admin.customer_review_contact_data') }}</h5>
                            <p class="text-muted">{{ trans('admin.customer_review_contact_help') }}</p>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="phone">{{ trans('base.phone') }}</label>
                                    <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" type="text" maxlength="19" value="{{ old('phone', $review?->phone) }}">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="email">Email</label>
                                    <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" maxlength="255" value="{{ old('email', $review?->email) }}">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="text-right">
                                <a href="{{ route('admin.customer-review.list.page') }}" class="btn btn-secondary">{{ trans('admin.back') }}</a>
                                <button type="submit" class="btn btn-dark">{{ trans('admin.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
