@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                    <div>
                        <h2 class="mb-1 page-title">{{ trans('admin.promo_codes') }}</h2>
                        <p class="card-text mb-0">{{ trans('admin.promo_codes_description') }}</p>
                    </div>
                    <a href="{{ route('admin.promo-code.create.page') }}" class="btn btn-dark mt-2">{{ trans('admin.promo_code_new') }}</a>
                </div>

                @if(Session::has('success'))
                    <div class="alert alert-success" role="alert">{{ Session::get('success') }}</div>
                @endif
                @if(Session::has('error'))
                    <div class="alert alert-danger" role="alert">{{ Session::get('error') }}</div>
                @endif

                <div class="card shadow my-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Код</th>
                                    <th>Знижка</th>
                                    <th>Товари</th>
                                    <th>Період дії</th>
                                    <th>Використання</th>
                                    <th>Статус</th>
                                    <th class="text-right">{{ trans('admin.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($promoCodesPaginated as $promoCode)
                                    @php
                                        $statusKey = $promoCode->statusKey();
                                        $statusBadge = match ($statusKey) {
                                            'active' => 'success',
                                            'scheduled' => 'info',
                                            'expired' => 'warning',
                                            'exhausted' => 'dark',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $promoCode->code }}</strong></td>
                                        <td>
                                            @if($promoCode->discount_type === App\Models\PromoCode::TYPE_FIXED)
                                                {{ number_format($promoCode->effectiveDiscountValue(), 0, ',', ' ') }} грн
                                            @else
                                                {{ rtrim(rtrim(number_format($promoCode->effectiveDiscountValue(), 2, '.', ''), '0'), '.') }}%
                                            @endif
                                        </td>
                                        <td>
                                            @if($promoCode->all_products)
                                                <strong>Усі товари</strong>
                                            @else
                                                <div class="d-flex flex-wrap" style="gap: 4px">
                                                    @if($promoCode->product_types_count)<span class="badge badge-light">Групи: {{ $promoCode->product_types_count }}</span>@endif
                                                    @if($promoCode->categories_count)<span class="badge badge-light">Категорії: {{ $promoCode->categories_count }}</span>@endif
                                                    @if($promoCode->brands_count)<span class="badge badge-light">Бренди: {{ $promoCode->brands_count }}</span>@endif
                                                    @if($promoCode->products_count)<span class="badge badge-light">Товари: {{ $promoCode->products_count }}</span>@endif
                                                </div>
                                            @endif
                                            @if($promoCode->max_discounted_items)
                                                <small class="d-block text-muted">До {{ $promoCode->max_discounted_items }} од.</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                {{ $promoCode->starts_at?->format('d.m.Y H:i') ?? 'Одразу' }}<br>
                                                {{ $promoCode->expires_at?->format('d.m.Y H:i') ?? 'Безстроково' }}
                                            </small>
                                        </td>
                                        <td>{{ $promoCode->usage_count }} / {{ $promoCode->usage_limit ?? '∞' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $statusBadge }}">
                                                {{ trans('admin.promo_code_status_'.$statusKey) }}
                                            </span>
                                        </td>
                                        <td class="text-right text-nowrap">
                                            <a class="btn btn-sm btn-outline-dark" href="{{ route('admin.promo-code.edit.page', $promoCode) }}">{{ trans('admin.edit') }}</a>
                                            <button class="btn btn-sm btn-outline-danger" type="button" data-toggle="modal" data-target="#deletePromoCode-{{ $promoCode->id }}">{{ trans('admin.delete') }}</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-5">Промокодів ще немає</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $promoCodesPaginated->links('pagination.admin') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach($promoCodesPaginated as $promoCode)
        <div class="modal fade" id="deletePromoCode-{{ $promoCode->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Видалити промокод {{ $promoCode->code }}?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Закрити"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">Якщо код уже є в замовленнях, він буде деактивований, а історія залишиться доступною.</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('admin.close') }}</button>
                        <form action="{{ route('admin.promo-code.delete', $promoCode) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger">{{ trans('admin.delete') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
