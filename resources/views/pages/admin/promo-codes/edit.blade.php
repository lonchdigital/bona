@extends('layouts.admin-main')

@section('content')
    @php
        $isEditing = isset($promoCode);
        $selectedProductIds = old('product_ids', $isEditing ? $promoCode->products->pluck('id')->all() : []);
        $selectedBrandIds = old('brand_ids', $isEditing ? $promoCode->brands->pluck('id')->all() : []);
        $selectedCategoryIds = old('category_ids', $isEditing ? $promoCode->categories->pluck('id')->all() : []);
        $selectedProductTypeIds = old('product_type_ids', $isEditing ? $promoCode->productTypes->pluck('id')->all() : []);
        $allProducts = (bool) old('all_products', $isEditing ? $promoCode->all_products : true);
        $isActive = (bool) old('is_active', $isEditing ? $promoCode->is_active : true);
    @endphp
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <h2 class="page-title">{{ trans($isEditing ? 'admin.promo_code_edit' : 'admin.promo_code_new') }}</h2>
                <p class="card-text">Порожній ліміт означає необмежене використання. Дати й час зберігаються за часовим поясом сайту.</p>

                @if(Session::has('success'))
                    <div class="alert alert-success" role="alert">{{ Session::get('success') }}</div>
                @endif

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <x-admin.reactive-form method="POST" action="{{ $isEditing ? route('admin.promo-code.edit', $promoCode) : route('admin.promo-code.create') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="code">Код <strong class="text-danger">*</strong></label>
                                        <div class="input-group">
                                            <input id="code" class="form-control text-uppercase" name="code" maxlength="64" required value="{{ old('code', $isEditing ? $promoCode->code : '') }}" autocomplete="off">
                                            <div class="input-group-append"><button class="btn btn-outline-secondary" type="button" id="generate-promo-code">Згенерувати</button></div>
                                        </div>
                                        <small class="form-text text-muted">Латинські літери, цифри, дефіс або підкреслення.</small>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-code"></div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-center pt-md-3">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="custom-control-input" type="checkbox" id="is-active" name="is_active" value="1" @checked($isActive)>
                                        <label class="custom-control-label" for="is-active">Активний</label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h5 class="mb-3">Розмір знижки</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="discount-type">Тип <strong class="text-danger">*</strong></label>
                                        <select id="discount-type" class="form-control" name="discount_type" required>
                                            <option value="percent" @selected(old('discount_type', $isEditing ? $promoCode->discount_type : 'percent') === 'percent')>Відсоток</option>
                                            <option value="fixed" @selected(old('discount_type', $isEditing ? $promoCode->discount_type : 'percent') === 'fixed')>Фіксована сума</option>
                                        </select>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-discount_type"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="discount-value">Значення <strong class="text-danger">*</strong></label>
                                        <input type="number" min="0.01" step="0.01" id="discount-value" class="form-control" name="discount_value" required value="{{ old('discount_value', $isEditing ? $promoCode->effectiveDiscountValue() : 10) }}">
                                        <div class="mt-1 text-danger ajaxError" id="error-field-discount_value"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="minimum-order">Мінімальна сума замовлення, грн</label>
                                        <input type="number" min="0" step="0.01" id="minimum-order" class="form-control" name="minimum_order_amount" value="{{ old('minimum_order_amount', $isEditing ? $promoCode->minimum_order_amount : 0) }}">
                                        <div class="mt-1 text-danger ajaxError" id="error-field-minimum_order_amount"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="maximum-discount">Максимальна сума знижки, грн</label>
                                        <input type="number" min="0.01" step="0.01" id="maximum-discount" class="form-control" name="maximum_discount_amount" value="{{ old('maximum_discount_amount', $isEditing ? $promoCode->maximum_discount_amount : '') }}">
                                        <small class="form-text text-muted">Необов’язково; корисно для відсоткової знижки.</small>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-maximum_discount_amount"></div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h5 class="mb-3">Строк та обмеження</h5>
                            <div class="row">
                                <div class="col-md-6"><div class="form-group"><label for="starts-at">Початок дії</label><input type="datetime-local" id="starts-at" class="form-control" name="starts_at" value="{{ old('starts_at', $isEditing ? $promoCode->starts_at?->format('Y-m-d\TH:i') : '') }}"><div class="mt-1 text-danger ajaxError" id="error-field-starts_at"></div></div></div>
                                <div class="col-md-6"><div class="form-group"><label for="expires-at">Кінець дії</label><input type="datetime-local" id="expires-at" class="form-control" name="expires_at" value="{{ old('expires_at', $isEditing ? $promoCode->expires_at?->format('Y-m-d\TH:i') : '') }}"><div class="mt-1 text-danger ajaxError" id="error-field-expires_at"></div></div></div>
                                <div class="col-md-6"><div class="form-group"><label for="usage-limit">Загальний ліміт використань</label><input type="number" min="1" id="usage-limit" class="form-control" name="usage_limit" value="{{ old('usage_limit', $isEditing ? $promoCode->usage_limit : '') }}"><div class="mt-1 text-danger ajaxError" id="error-field-usage_limit"></div></div></div>
                                <div class="col-md-6"><div class="form-group"><label for="max-items">Максимум одиниць товару зі знижкою</label><input type="number" min="1" id="max-items" class="form-control" name="max_discounted_items" value="{{ old('max_discounted_items', $isEditing ? $promoCode->max_discounted_items : '') }}"><div class="mt-1 text-danger ajaxError" id="error-field-max_discounted_items"></div></div></div>
                            </div>

                            <hr class="my-4">
                            <h5 class="mb-2">Область дії промокоду</h5>
                            <p class="text-muted small mb-3">Групи поєднуються за правилом «АБО»: знижка діятиме на товар, якщо він входить хоча б в одну обрану групу, категорію, бренд або список товарів.</p>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="hidden" name="all_products" value="0">
                                <input class="custom-control-input" type="checkbox" id="all-products" name="all_products" value="1" @checked($allProducts)>
                                <label class="custom-control-label" for="all-products">Застосовувати до всіх товарів</label>
                            </div>
                            <div id="product-selection" @if($allProducts) style="display:none" @endif>
                                <div class="alert alert-light border py-2 px-3 mb-3" role="status" data-scope-summary>
                                    Оберіть потрібні групи нижче. Пошук працює в кожному полі.
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="product-type-ids">Групи товарів</label>
                                            <select id="product-type-ids" class="form-control scope-select" name="product_type_ids[]" multiple data-placeholder="Знайти групу товарів">
                                                @foreach($productTypes as $productType)
                                                    <option value="{{ $productType->id }}" @selected(in_array($productType->id, $selectedProductTypeIds))>{{ $productType->name }}</option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">Наприклад: міжкімнатні двері, ручки або стінові панелі.</small>
                                            <div class="mt-1 text-danger ajaxError" id="error-field-product_type_ids"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="category-ids">Категорії</label>
                                            <select id="category-ids" class="form-control scope-select" name="category_ids[]" multiple data-placeholder="Знайти категорію">
                                                @foreach($categories->groupBy('product_type_id') as $typeCategories)
                                                    <optgroup label="{{ $typeCategories->first()->productType?->name }}">
                                                        @foreach($typeCategories as $category)
                                                            <option value="{{ $category->id }}" @selected(in_array($category->id, $selectedCategoryIds))>{{ $category->name }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                            <div class="mt-1 text-danger ajaxError" id="error-field-category_ids"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="brand-ids">Бренди</label>
                                            <select id="brand-ids" class="form-control scope-select" name="brand_ids[]" multiple data-placeholder="Знайти бренд">
                                                @foreach($brands as $brand)
                                                    <option value="{{ $brand->id }}" @selected(in_array($brand->id, $selectedBrandIds))>{{ $brand->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="mt-1 text-danger ajaxError" id="error-field-brand_ids"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="product-ids">Окремі товари</label>
                                            <select id="product-ids" class="form-control" name="product_ids[]" multiple>
                                                @foreach($selectedProducts as $product)
                                                    <option value="{{ $product->id }}" selected>#{{ $product->id }} — {{ $product->name }} · {{ $product->sku }}</option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">Введіть щонайменше 3 символи назви, SKU або ID.</small>
                                            <div class="mt-1 text-danger ajaxError" id="error-field-product_ids"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-1 text-danger ajaxError" id="error-field-scope"></div>
                            </div>

                            @if($isEditing)
                                <p class="text-muted small">Використано: {{ $promoCode->usage_count }}{{ $promoCode->usage_limit ? ' із '.$promoCode->usage_limit : '' }}.</p>
                            @endif

                            <div class="text-right mt-4">
                                <a href="{{ route('admin.promo-code.list.page') }}" class="btn btn-secondary">{{ trans('admin.back') }}</a>
                                <button type="submit" class="btn btn-dark">{{ trans('admin.save') }}</button>
                            </div>
                        </x-admin.reactive-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            var alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            $('#generate-promo-code').on('click', function () {
                var code = 'BONA-';
                for (var i = 0; i < 8; i += 1) code += alphabet.charAt(Math.floor(Math.random() * alphabet.length));
                $('#code').val(code).trigger('input');
            });
            $('#code').on('input', function () { this.value = this.value.toUpperCase().replace(/[^A-Z0-9_-]/g, ''); });
            function updateScopeSummary() {
                var labels = [];
                var fields = [
                    ['#product-type-ids', 'груп'],
                    ['#category-ids', 'категорій'],
                    ['#brand-ids', 'брендів'],
                    ['#product-ids', 'товарів']
                ];

                fields.forEach(function (field) {
                    var count = ($(field[0]).val() || []).length;
                    if (count) labels.push(count + ' ' + field[1]);
                });

                $('[data-scope-summary]').text(labels.length ? 'Обрано: ' + labels.join(' · ') : 'Оберіть хоча б одну групу, категорію, бренд або товар.');
            }

            $('#all-products').on('change', function () {
                $('#product-selection').toggle(!this.checked);
                updateScopeSummary();
            });

            if ($.fn.select2) {
                $('.scope-select').each(function () {
                    $(this).select2({
                        width: '100%',
                        placeholder: $(this).data('placeholder'),
                        closeOnSelect: false
                    });
                });

                $('#product-ids').select2({
                    width: '100%',
                    placeholder: 'Знайти товар',
                    minimumInputLength: 3,
                    closeOnSelect: false,
                    ajax: {
                        url: @json(route('admin.product.list.all')),
                        dataType: 'json',
                        delay: 250,
                        data: function (params) { return { query: params.term }; },
                        processResults: function (response) { return { results: response.data || [] }; },
                        cache: true
                    }
                });

                $('.scope-select, #product-ids').on('change', updateScopeSummary);
            }

            updateScopeSummary();
        });
    </script>
@endpush
