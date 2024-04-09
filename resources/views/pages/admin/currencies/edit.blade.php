@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                @isset($currency)
                    <h2 class="page-title">{{ trans('admin.currency_edit') }}</h2>
                @else
                    <h2 class="page-title">{{ trans('admin.currency_new') }}</h2>
                @endisset
                <div class="card shadow mb-4">
                    <div id="form-header" class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.currency_information') }}</strong>
                        <x-admin.multilanguage-switch/>
                    </div>
                    <div class="card-body">
                        <x-admin.reactive-form method="POST" action="{{ isset($currency) ?  route('admin.currency.edit', ['currency' => $currency->id]) : route('admin.currency.create') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <x-admin.multilanguage-input :is-required="true" :label="trans('admin.currency_name')" field-name="name" :values="isset($currency) ? $currency->getTranslations('name') : []"/>
                                    <x-admin.multilanguage-input  :is-required="true" label="{{ trans('admin.currency_name_short') }} ({{ trans('admin.currency_name_short_description') }})" field-name="name_short" :values="isset($currency)  ? $currency->getTranslations('name_short') : []"/>
                                </div>
                            </div>

                            <!-- SLUG -->
                            <div class="form-group mb-3">
                                <label for="code">{{ trans('admin.currency_code') }} <strong
                                        class="text-danger">*</strong></label>
                                <input type="text" id="code" name="code" class="form-control"
                                       @isset($currency) value="{{ $currency->code }}" @endisset>
                                <div class="mt-1 text-danger ajaxError" id="error-field-code"></div>
                            </div>

                            <!-- IS BASE CURRENCY -->
                            <div class="custom-control custom-checkbox mb-1">
                                <input type="hidden" name="is_base" value="0">
                                <input class="custom-control-input" value="1" type="checkbox" id="is-base" name="is_base" @if((isset($currency) && $currency->is_base)) checked @endif>
                                <label class="custom-control-label" for="is-base">{{ trans('admin.currency_is_base') }}</label>
                            </div>
                            <div class="row mb-2">
                                <div class="col">
                                    <div class="mt-1 text-danger ajaxError" id="error-field-is_base"></div>
                                </div>
                            </div>

                            <!-- CURRENCY RATE -->
                            <div class="form-group mb-3 @if(isset($currency) && $currency->is_base) d-none @endif" id="currency-rate-group">
                                <label for="rate">{{ trans('admin.currency_rate') }} ({{ trans('admin.currency_rate_explanation') }}) <strong
                                        class="text-danger">*</strong></label>
                                <div class="input-group">
                                    <input type="text" id="rate" name="rate" class="form-control input-money"
                                           aria-describedby="price-currency"
                                           @isset($currency) value="{{ number_format($currency->rate, 2, '.', '') }}" @endisset>
                                </div>
                                <div class="mt-1 text-danger ajaxError" id="error-field-rate"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <a href="{{ route('admin.currency.list.page') }}" class="btn btn-secondary">{{ trans('admin.back') }}</a>
                                    <button type="submit" class="btn btn-dark">{{ trans('admin.save') }}</button>
                                </div>
                            </div>
                        </x-admin.reactive-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type='text/javascript'>
        $(document).ready(function () {
            const isBaseCheckBox = $('#is-base');

            isBaseCheckBox.change(function () {
                if ($(this).is(':checked')) {
                    $('#currency-rate-group').addClass('d-none');
                } else {
                    $('#currency-rate-group').removeClass('d-none');
                }
            });

            $('.input-money').mask("###0.00", {
                reverse: true
            });
        });
    </script>
@endpush

