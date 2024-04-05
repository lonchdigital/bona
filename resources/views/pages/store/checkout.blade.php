@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . trans('base.order_creation') }}</title>
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['#' => 'checkout']])

    <main id="checkout" class="checkout">
        <div class="content">
            <div class="entry-content common-page-section-wrapper art-section-pd">
                <div class="container">

                    <div class="row">
                        <header class=" col-12 art-header-left">
                            <div>
                                <h1 class="title">{{ trans('base.checkout') }}</h1>
                            </div>
                        </header>
                    </div>


                    <form class="row checkout-main mb-lg-4" id="checkout-main" action="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.checkout.confirm') }}" method="POST">
                        <div class="left-content col-lg-8">
                            <!-- checkout-order-data -->
                            <div id="checkout-order-data" class="checkout-order-data">
                                <div class="checkout-order-data-form flex-column mb-10 mb-lg-20">
                                    @csrf
                                    @guest
                                        <div class="flex-column flex-lg-row pr-xl-18 mt-10 mt-lg-4 mt-xxl-26 mb-4 mb-lg-10">
                                            <div class="">
                                                <div class="h4 mb-4 d-none d-lg-block">{{ trans('base.personal_data') }}</div>
                                            </div>
                                            <div class="">
                                                <div class="checkout-personal-data pr-xl-18 mb-4 mb-lg-10">
                                                    <div class="art-row-line mb-1">
                                                        <div class="col-6">
                                                            <div class="field @if($errors->has('first_name')) field-error @endif mr-n1">
                                                                <input type="text" class="art-form-light-control" id="name"
                                                                       name="first_name"
                                                                       placeholder="{{ trans('base.name') }}"
                                                                       value="{{ old('first_name') }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="field @if($errors->has('last_name')) field-error @endif ml-n1">
                                                                <input type="text" class="art-form-light-control" id="surname"
                                                                       name="last_name"
                                                                       placeholder="{{ trans('base.last_name') }}"
                                                                       value="{{ old('last_name') }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-5">
                                                        <div class="col-12 text-danger">
                                                            @error('first_name')
                                                            <div class="field-error-help-descr">{{ $message }}</div>
                                                            @enderror
                                                            @error('last_name')
                                                            <div class="field-error-help-descr">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="field  @if($errors->has('phone')) field-error @endif  mb-1 mb-lg-1 mb-xl-1 d-flex flex-column-reverse">
                                                        <input type="tel" class="art-form-light-control" id="phone" name="phone"
                                                               placeholder="{{ trans('base.phone') }}"
                                                               value="{{ old('phone') }}">
                                                    </div>
                                                    <div class="row mb-5">
                                                        <div class="col-12 text-danger">
                                                            @error('phone')
                                                            <div class="field-error-help-descr">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="field @if($errors->has('email')) field-error @endif mb-1 d-flex flex-column">
                                                        <input type="email" class="art-form-light-control" id="email" name="email"
                                                               placeholder="{{ trans('base.email') }}"
                                                               value="{{ old('email') }}">
                                                    </div>
                                                    <div class="row mb-1">
                                                        <div class="col-12 text-danger">
                                                            @error('email')
                                                            <div class="field-error-help-descr">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    @endguest

                                    <div class="checkout-delivery d-flex flex-column flex-xl-row pr-xl-18 mt-10 mt-lg-4 mt-xxl-26 mb-4 mb-lg-10">
                                        <div class="checkout-delivery-accordion w-100 mt-1" id="checkout-delivery-accordion">

                                            <h4>{{ trans('base.delivery') }}</h4>

                                            <div class="card delivery-address">
                                                <div class="card-header">
                                                    <div class="card-header-row">
                                                        <div class="card-header-left checkbox">
                                                            <div class="position-relative">
                                                                <input data-accordion="delivery-1"
                                                                       class="art-accordion-delivery"
                                                                       type="radio"
                                                                       @if(old('delivery_type_id') == App\DataClasses\DeliveryTypesDataClass::ADDRESS_DELIVERY) checked
                                                                       @endif id="delivery-radio-address"
                                                                       name="delivery_type_id"
                                                                       value="{{ App\DataClasses\DeliveryTypesDataClass::ADDRESS_DELIVERY }}"
                                                                >
                                                                <label class="custom-control-label" for="delivery-radio-address">{{ trans('base.checkout_address_delivery') }}</label>
                                                            </div>
                                                        </div>
                                                        <div class="card-header-right">
                                                            <div class="nav-item-info d-flex align-items-center justify-content-between">
                                                                {{--
                                                                <div class="delivery-free mr-3">{{ config('domain.delivery_price') }}грн.</div>
                                                                --}}

                                                                <div class="i-info" data-toggle="tooltip"
                                                                     title="<span class='help'>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dicta fuga quasi numquam nesciunt consequuntur ullam odio iure ut repellat! Libero mollitia perferendis magni minima. Quae pariatur maiores recusandae minima accusantium.</span>">
                                                                    <span class="icon-i-info"><span
                                                                            class="path1"></span><span
                                                                            class="path2"></span></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="delivery-1"
                                                     class="accordion-delivery-data @if(old('delivery_type_id') == App\DataClasses\DeliveryTypesDataClass::ADDRESS_DELIVERY)art-show @else art-hide @endif"
                                                     data-parent="#checkout-delivery-accordion">
                                                    <div class="card-body pt-5 px-0 pb-5 mb-4">
                                                        <div class="delivery-title mb-4">{{ trans('base.checkout_select_city') }}:</div>
                                                        <div class="delivery-title mb-4">{{ trans('base.checkout_address_to_delivery') }}</div>
                                                        <div class="city-search-wrap">
                                                            <div class="field @if($errors->has('region_id')) field-error @endif city-search mb-1">
                                                                <select class="region-select" name="region_id">
                                                                    <option disabled @if(!old('region_id')) selected @endif value="">{{ trans('base.checkout_select_region') }}</option>
                                                                    @foreach($regions as $region)
                                                                        <option @if(old('region_id') == $region->id) selected @endif value="{{ $region->id }}">{{ $region->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-12 text-danger">
                                                                    @error('region_id')
                                                                    <div
                                                                        class="field-error-help-descr">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <div class="row mb-1">
                                                            <div class="col-12">
                                                                <div class="field @if($errors->has('district')) field-error @endif">
                                                                    <input type="text" name="district"
                                                                           class="art-form-light-control"
                                                                           placeholder="{{ trans('base.district') }}"
                                                                           value="{{ old('district') }}">
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <div class="row mb-3">
                                                            <div class="col-12 text-danger">
                                                                @error('district')
                                                                <div
                                                                    class="field-error-help-descr">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="row mb-1">
                                                            <div class="col-12">
                                                                <div
                                                                    class="field @if($errors->has('city')) field-error @endif">
                                                                    <input type="text" name="city"
                                                                           class="art-form-light-control"
                                                                           placeholder="{{ trans('base.city') }}"
                                                                           value="{{ old('city') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-12 text-danger">
                                                                @error('city')
                                                                <div
                                                                    class="field-error-help-descr">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="address-search-wrap mb-10 mb-sm-0">
                                                            <div
                                                                class="field @if($errors->has('street')) field-error @endif field--house">
                                                                <input type="text" name="street"
                                                                       class="art-form-light-control"
                                                                       placeholder="{{ trans('base.checkout_street') }}"
                                                                       value="{{ old('street') }}">
                                                            </div>
                                                            <div
                                                                class="field @if($errors->has('building_number')) field-error @endif field--house">
                                                                <input type="text" name="building_number"
                                                                       class="art-form-light-control"
                                                                       placeholder="{{ trans('base.checkout_building_number') }}"
                                                                       value="{{ old('building_number') }}">
                                                            </div>
                                                            <div
                                                                class="field @if($errors->has('apartment_number')) field-error @endif field--apart">
                                                                <input type="text" name="apartment_number"
                                                                       class="art-form-light-control"
                                                                       placeholder="{{ trans('base.checkout_apartment_number') }}"
                                                                       value="{{ old('apartment_number') }}">
                                                            </div>
                                                            <div
                                                                class="field @if($errors->has('floor_number')) field-error @endif field--floor">
                                                                <input type="text" name="floor_number"
                                                                       class="art-form-light-control"
                                                                       placeholder="{{ trans('base.checkout_floor_number') }}"
                                                                       value="{{ old('floor_number') }}">
                                                            </div>
                                                        </div>
                                                        <div class="row my-1">
                                                            <div class="col-12 text-danger">
                                                                @error('street')
                                                                <div
                                                                    class="field-error-help-descr">{{ $message }}</div>
                                                                @enderror
                                                                @error('building_number')
                                                                <div
                                                                    class="field-error-help-descr">{{ $message }}</div>
                                                                @enderror
                                                                @error('apartment_number')
                                                                <div
                                                                    class="field-error-help-descr">{{ $message }}</div>
                                                                @enderror
                                                                @error('floor_number')
                                                                <div
                                                                    class="field-error-help-descr">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>


                                                        <div class="row mt-1">
                                                            <div class="col-12 text-danger">
                                                                @error('delivery_date')
                                                                <div
                                                                    class="field-error-help-descr">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card delivery-np">
                                                <div class="card-header">
                                                    <div class="card-header-row">
                                                        <div class="card-header-left checkbox">
                                                            <div class="position-relative">
                                                                <input data-accordion="delivery-2"
                                                                       class="art-accordion-delivery"
                                                                       type="radio"
                                                                       id="delivery-radio-np"
                                                                       @if(old('delivery_type_id') == App\DataClasses\DeliveryTypesDataClass::NP_DELIVERY) checked
                                                                       @endif name="delivery_type_id"
                                                                       value="{{ App\DataClasses\DeliveryTypesDataClass::NP_DELIVERY }}"
                                                                >
                                                                <label class="custom-control-label"
                                                                       for="delivery-radio-np">
                                                                    <div class="i-np mr-2">
                                                                        <svg>
                                                                            <use
                                                                                xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-np"></use>
                                                                        </svg>
                                                                    </div>
                                                                    {{ trans('base.checkout_np_delivery') }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="card-header-right">
                                                            <div
                                                                class="nav-item-info d-flex align-items-center justify-content-between">
                                                                <div
                                                                    class="delivery-free mr-3">{{ trans('base.cart_delivery_price') }}</div>
                                                                <div class="i-info" data-toggle="tooltip"
                                                                     title="<span class='help'>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dicta fuga quasi numquam nesciunt consequuntur ullam odio iure ut repellat! Libero mollitia perferendis magni minima. Quae pariatur maiores recusandae minima accusantium.</span>">
                                                                    <span class="icon-i-info"><span
                                                                            class="path1"></span><span
                                                                            class="path2"></span></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="delivery-2"
                                                     class="accordion-delivery-data @if(old('delivery_type_id') == App\DataClasses\DeliveryTypesDataClass::NP_DELIVERY) art-show @else art-hide @endif"
                                                     data-parent="#checkout-delivery-accordion">
                                                    <div class="card-body pt-5 px-0 pb-5 mb-4">
                                                        <div
                                                            class="delivery-title mb-4">{{ trans('base.checkout_select_np_department') }}</div>
                                                        <div class="city-search-wrap">
                                                            <div
                                                                class="field @if($errors->has('np_city')) field-error @endif city-search mb-1">
                                                                <input value="{{ old('np_city') }}"
                                                                       @if(old('np_city')) data-initial-value='{{ json_encode(app()->make(\App\Services\Delivery\DeliveryService::class)->getNpCityByRef(old('np_city'))) }}'
                                                                       @endif type="text" class="np-city-select"
                                                                       name="np_city"/>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-12 text-danger">
                                                                    @error('np_city')
                                                                    <div
                                                                        class="field-error-np_area">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="np-department-search-wrap">
                                                            <div
                                                                class="field @if($errors->has('np_department')) field-error @endif city-search mb-1">
                                                                <input value="{{ old('np_department') }}"
                                                                       @if(old('np_department')) data-initial-value='{{ json_encode(app()->make(\App\Services\Delivery\DeliveryService::class)->getNpDepartmentByRef(old('np_city'), old('np_department'))) }}'
                                                                       @endif  type="text"
                                                                       class="np-department-select"
                                                                       name="np_department"/>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-12 text-danger">
                                                                    @error('np_department')
                                                                    <div
                                                                        class="field-error-np_area">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card delivery-sat">
                                                <div class="card-header">
                                                    <div class="card-header-row">
                                                        <div class="card-header-left checkbox">
                                                            <div class="position-relative">
                                                                <input data-accordion="delivery-3"
                                                                       class="art-accordion-delivery"
                                                                       type="radio"
                                                                       @if(old('delivery_type_id') == App\DataClasses\DeliveryTypesDataClass::SAT_DELIVERY) checked
                                                                       @endif id="delivery-radio-sat"
                                                                       name="delivery_type_id"
                                                                       value="{{ App\DataClasses\DeliveryTypesDataClass::SAT_DELIVERY }}"
                                                                >
                                                                <label class="custom-control-label" for="delivery-radio-sat">{{ trans('base.checkout_sat_delivery') }}</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="delivery-3"
                                                     class="accordion-delivery-data @if(old('delivery_type_id') == App\DataClasses\DeliveryTypesDataClass::SAT_DELIVERY) art-show @else art-hide @endif"
                                                     data-parent="#checkout-delivery-accordion">
                                                    <div class="card-body pt-5 px-0 pb-5 mb-4">
                                                        <div
                                                            class="delivery-title mb-4">{{ trans('base.checkout_select_city') }}
                                                            :
                                                        </div>
                                                        <div
                                                            class="delivery-title mb-4">{{ trans('base.checkout_address_to_delivery') }}</div>
                                                        <div class="city-search-wrap">
                                                            <div
                                                                class="field @if($errors->has('sat_region_id')) field-error @endif city-search mb-1">
                                                                <select class="region-select" name="sat_region_id">
                                                                    <option disabled
                                                                            @if(!old('sat_region_id')) selected @endif value="">{{ trans('base.checkout_select_region') }}</option>
                                                                    @foreach($regions as $region)
                                                                        <option
                                                                            @if(old('sat_region_id') == $region->id) selected
                                                                            @endif value="{{ $region->id }}">{{ $region->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-12 text-danger">
                                                                    @error('sat_region_id')
                                                                    <div
                                                                        class="field-error-help-descr">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-1">
                                                            <div class="col-12">
                                                                <div
                                                                    class="field @if($errors->has('sat_district')) field-error @endif">
                                                                    <input type="text"
                                                                           name="sat_district"
                                                                           class="art-form-light-control"
                                                                           placeholder="{{ trans('base.district') }}"
                                                                           value="{{ old('sat_district') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-12 text-danger">
                                                                @error('sat_district')
                                                                <div
                                                                    class="field-error-help-descr">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="row mb-1">
                                                            <div class="col-12">
                                                                <div
                                                                    class="field @if($errors->has('sat_city')) field-error @endif">
                                                                    <input type="text"
                                                                           name="sat_city"
                                                                           class="art-form-light-control"
                                                                           placeholder="{{ trans('base.city') }}"
                                                                           value="{{ old('sat_city') }}">
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>



                                    <div class="checkout-delivery d-flex flex-column flex-xl-row checkout-payment pr-xl-18 mb-10 mb-lg-10">
                                        <div class="w-100 checkbox">
                                            <div class="mt-1">
                                                <div class="">
                                                    <h4>{{ trans('base.checkout_payment') }}</h4>
                                                    <div class="delivery-title mb-4">{{ trans('base.checkout_payment_upon_receipt') }}
                                                        :
                                                    </div>
                                                    <div class="checkbox art-mb-10">
                                                        <div class="position-relative">
                                                            <input type="radio"
                                                                   @if(!old('payment_type_id') || old('payment_type_id') == App\DataClasses\PaymentTypesDataClass::CASH_PAYMENT) checked
                                                                   @endif id="payment-cash" name="payment_type_id"
                                                                   value="{{ App\DataClasses\PaymentTypesDataClass::CASH_PAYMENT }}">
                                                            <label class="custom-control-label"
                                                                   for="payment-cash">{{ trans('base.checkout_payment_cash') }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <div class="i-info ml-auto" data-toggle="tooltip"
                                                         title="<span class='help'>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dicta fuga quasi numquam nesciunt consequuntur ullam odio iure ut repellat! Libero mollitia perferendis magni minima. Quae pariatur maiores recusandae minima accusantium.</span>">
                                                        <span class="icon-i-info"><span
                                                                class="path1"></span><span class="path2"></span></span>
                                                    </div>
                                                </div>
                                            </div>
                                                {{-- TODO:: add payment cart --}}
<!--                                            <div class="">
                                                <div class="">
                                                    <div class="checkbox art-mb-10">
                                                        <div class="position-relative">
                                                            <input type="radio"
                                                                   @if(old('payment_type_id') == App\DataClasses\PaymentTypesDataClass::CARD_PAYMENT) checked
                                                                   @endif id="payment-card" name="payment_type_id"
                                                                   value="{{ App\DataClasses\PaymentTypesDataClass::CARD_PAYMENT }}">
                                                            <label class="custom-control-label"
                                                                   for="payment-card">{{ trans('base.checkout_payment_card') }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>-->
                                        </div>
                                    </div>

                                    <div class="checkout-delivery d-flex flex-column flex-xl-row checkout-recipient pr-xl-18">
                                        <div class="w-100 checkbox">

                                            <h4>{{ trans('base.checkout_recipient') }}</h4>

                                            <div class="mt-1">
                                                <div class="checkbox art-mb-10">
                                                    <div class="position-relative">
                                                        <input data-accordion="recipient-1"
                                                               class="art-accordion-recipient"
                                                               type="radio"
                                                               @if(!old('recipient_type_id') || (old('recipient_type_id') == \App\DataClasses\RecipientTypesDataClass::RECIPIENT_USER)) checked
                                                               @endif id="recipient-user"
                                                               name="recipient_type_id"
                                                               value="{{ \App\DataClasses\RecipientTypesDataClass::RECIPIENT_USER }}"/>
                                                        <label data-toggle="collapse"
                                                               data-target="#collapse-self-recipient"
                                                               for="recipient-user">{{ trans('base.checkout_recipient_me') }}</label>
                                                    </div>
                                                </div>
                                                <div class="">
                                                    <div class="position-relative">
                                                        <input data-accordion="recipient-2"
                                                               class="art-accordion-recipient"
                                                               type="radio"
                                                               @if((old('recipient_type_id') == \App\DataClasses\RecipientTypesDataClass::RECIPIENT_CUSTOM)) checked
                                                               @endif id="recipient-other"
                                                               name="recipient_type_id"
                                                               value="{{ \App\DataClasses\RecipientTypesDataClass::RECIPIENT_CUSTOM }}"/>
                                                        <label data-toggle="collapse"
                                                               data-target="#collapse-custom-recipient"
                                                               for="recipient-other">{{ trans('base.checkout_recipient_another_person') }}</label>
                                                    </div>
                                                </div>

                                                <div class="" id="checkout-custom-recipient-accordion">
                                                    <div id="recipient-2"
                                                         class="accordion-recipient-data  @if((old('recipient_type_id') == \App\DataClasses\RecipientTypesDataClass::RECIPIENT_CUSTOM)) art-show @else art-hide @endif"
                                                         data-parent="#checkout-custom-recipient-accordion">
                                                        <div class="checkout-personal-data mb-6 mb-xl-0 pt-5">
                                                            <div class="art-row-line mb-1">
                                                                <div class="col-6">
                                                                    <div
                                                                        class="field @if($errors->has('custom_first_name')) field-error @endif mr-n1">
                                                                        <input type="text" class="art-form-light-control"
                                                                               id="custom_name"
                                                                               name="custom_first_name"
                                                                               placeholder="{{ trans('base.name') }}"
                                                                               value="{{ old('custom_first_name') }}">
                                                                        <label class="form-label d-none mb-1"
                                                                               for="custom_name">{{ trans('base.name') }}</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div
                                                                        class="field @if($errors->has('custom_last_name')) field-error @endif ml-n1">
                                                                        <input type="text" class="art-form-light-control"
                                                                               id="custom_surname"
                                                                               name="custom_last_name"
                                                                               placeholder="{{ trans('base.last_name') }}"
                                                                               value="{{ old('custom_last_name') }}">
                                                                        <label class="form-label d-none mb-1"
                                                                               for="custom_surname">{{ trans('base.last_name') }}</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mb-5">
                                                                <div class="col-12 text-danger">
                                                                    @error('custom_first_name')
                                                                    <div
                                                                        class="field-error-help-descr">{{ $message }}</div>
                                                                    @enderror
                                                                    @error('custom_last_name')
                                                                    <div
                                                                        class="field-error-help-descr">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="field  @if($errors->has('custom_phone')) field-error @endif  mb-1 mb-lg-1 mb-xl-1 d-flex flex-column-reverse">
                                                                <input type="tel" class="art-form-light-control"
                                                                       id="custom_phone" name="custom_phone"
                                                                       placeholder="{{ trans('base.phone') }}"
                                                                       value="{{ old('custom_phone') }}">
                                                            </div>
                                                            <div class="row mb-5">
                                                                <div class="col-12 text-danger">
                                                                    @error('custom_phone')
                                                                    <div
                                                                        class="field-error-help-descr">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="field @if($errors->has('custom_email')) field-error @endif mb-1 d-flex flex-column">
                                                                <input type="email" class="art-form-light-control"
                                                                       id="custom_email" name="custom_email"
                                                                       placeholder="{{ trans('base.email') }}"
                                                                       value="{{ old('custom_email') }}">
                                                            </div>
                                                            <div class="row mb-1">
                                                                <div class="col-12 text-danger">
                                                                    @error('custom_email')
                                                                    <div
                                                                        class="field-error-help-descr">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="">
                                                    <div class="delivery-title mb-3 mt-4">{{ trans('base.checkout_order_comment') }}</div>
                                                    <div class="field mb-4">
                                                        <textarea class="art-form-light-control h-100" name="comment"
                                                                  rows="4"
                                                                  placeholder="{{ trans('base.checkout_order_comment_placeholder') }}"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="right-sidebar col-lg-4">
                            <!-- checkout-order-info -->
                            <div id="checkout-sidebar" class="checkout-sidebar">
                                <div id="checkout-order-info" class="checkout-order-info">
                                    <div class="checkout-order-info-form">
                                        <div class="total-info-top w-100">
                                            <div class="total-info-top-header d-flex align-items-center justify-content-between mb-4">
                                                <div class="h4 art-total-info-title">{{ trans('base.checkout_my_order') }}</div>
                                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.cart.page') }}" class="btn btn-edit p-0 m-0">
                                                    <div class="i-gear mr-2">
                                                        <svg>
                                                            <use
                                                                xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-gear"></use>
                                                        </svg>
                                                    </div>
                                                    <span>{{ trans('base.checkout_edit_order') }}</span>
                                                </a>
                                            </div>

                                            <div class="checkout-order-list-product-descr art-checkout-order-list ">
                                                <div class="row">
                                                    <div class="col checkout-product-list">
                                                        @foreach($productsInCart as $product)
                                                            <div class="list-product-item mb-2">
                                                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]) }}"
                                                                   class="table-product d-flex align-items-center">
                                                                    <div class="table-product-image mr-3 d-block">
                                                                        <img src="{{ $product->main_image_url }}" alt="img">
                                                                    </div>
                                                                    <div class="table-product-info d-block">
                                                                        <div class="table-product-name mb-0 d-block">
                                                                            @if($product->pivot->count > 1)
                                                                                {{ $product->name }}<span>{{ ' ( x'.$product->pivot->count.' )' }}</span>
                                                                            @else
                                                                                {{ $product->name }}
                                                                            @endif
                                                                        </div>
                                                                        {{--@if($product->pivot->attributes)
                                                                            <div class="product-attributes">
                                                                                @foreach(json_decode($product->pivot->attributes) as $key => $value)
                                                                                    <div class="product-attribute-line">
                                                                                        <div class="attribute-value">{{ $value }}</div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif--}}
                                                                        <div class="table-total-price position-relative">
                                                                            <div class="price">
{{--                                                                                {{ $product->price }} {{ $baseCurrency->name_short }}--}}
                                                                                {{ $product->price + $product->pivot->attributes_price }} {{ $baseCurrency->name_short }}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div
                                                    class="checkout-payment checkout-order-info-delivery-title mb-1 pt-1">{{ trans('base.checkout_payment') }}
                                                    :&nbsp;<span
                                                        class="selected-payment-type">{{ trans('base.checkout_payment_cash') }}</span>
                                                </div>
                                                <div class="checkout-delivery checkout-order-info-delivery-title mb-1 pt-1">{{ trans('base.delivery') }}
                                                    : <span class="selected-delivery-type">{{ trans('base.checkout_address_delivery') }}</span>
                                                </div>
                                            </div>
                                            <div class="info-top-prices mb-3">
                                                <div class="info-top-item mb-1 pt-1">
                                                    <span class="">{{ trans('base.products_price') }}: </span>
                                                    <span class="text-nowrap price-products"></span>
                                                </div>

                                                {{-- TODO: Price delivery --}}
                                            {{--
                                            <div class="info-top-item pb-3 normal-delivery">
                                                <span class="mr-6">{{ trans('base.delivery') }}</span>
                                                <span class="text-nowrap price-delivery"></span>


<!--                                                    <div class="d-flex">
                                                        <span class="old-price-delivery text-nowrap"></span>
                                                        <span class="text-nowrap price-delivery"></span>
                                                    </div>-->
                                                </div>
                                            --}}
                                                <div class="info-top-item mb-1 pt-1">
                                                    <span class="">{{ trans('base.products_price_discount') }}: </span>
                                                    <span class="text-nowrap price-discount"></span>
                                                </div>
                                                <div class="info-top-item pb-3 normal-total pt-1">
                                                    <span class="total-title-delivery">{{ trans('base.products_price_total') }}: </span>
                                                    <span class="text-nowrap total-price-delivery"></span>
                                                </div>
                                            </div>
                                            <hr class="pb-2">

                                            <div class="total-info-bottom">

                                                <div class="agreement-wrapper">
                                                    <div class="custom-control custom-checkbox position-relative art-checkout-agreement">
                                                        <input type="hidden" name="agreement" value="0">
                                                        <input type="checkbox" id="checkout-order-info-form-check" name="agreement" value="1">
                                                        <label for="checkout-order-info-form-check">{{ trans('base.checkout_by_confirm_i_agree') }}
                                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => 'dogovir-publichnoyi-oferti']) }}">{{ mb_strtolower(trans('base.conditions')) }}</a>
                                                        </label>
                                                    </div>
                                                    @error('agreement')
                                                    <div class="row">
                                                        <div class="col-12 text-danger">
                                                            {{ $message }}
                                                        </div>
                                                    </div>
                                                    @enderror
                                                </div>

                                                <div class="buttons-wrapper">
                                                    <button type="submit" class="btn btn-black-custom w-100 mb-4">{{ trans('base.checkout_confirm_order') }}</button>
                                                    <div class="info-top-pay text-center d-flex flex-column flex-lg-row justify-content-center mb-lg-6">
                                                        <div class="pay-title mr-lg-2 mb-2 mb-lg-0">{{ trans('base.payments_methods') }}:</div>
                                                        <div class="pay-list d-flex align-items-center justify-content-center">
                                                            <div class="pay-list-item overflow-hidden d-flex align-items-center justify-content-center">
                                                                <img src="{{ Vite::asset('resources/img/payment/visa.svg') }}" alt="img">
                                                            </div>
                                                            <div class="pay-list-item overflow-hidden d-flex align-items-center justify-content-center">
                                                                <img src="{{ Vite::asset('resources/img/payment/mastercard.svg') }}" alt="img">
                                                            </div>
                                                            <div class="pay-list-item overflow-hidden d-flex align-items-center justify-content-center">
                                                                <img src="{{ Vite::asset('resources/img/payment/cash.svg') }}" alt="img">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="info-bottom-title mb-7 text-center d-none">
                                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.cart.page') }}">{{ trans('base.checkout_apply_promo_code') }}</a>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection
