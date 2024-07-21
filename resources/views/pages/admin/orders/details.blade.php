@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                    <h2 class="page-title">{{ trans('admin.order_id') }}{{ $order->id }}</h2>
                <div class="card shadow mb-4">
                    <div id="form-header" class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.order_details') }}</strong>
                    </div>
                    <div class="card-body">
                        <x-admin.reactive-form method="POST" action="{{ route('admin.order.edit', ['order' => $order->id]) }}">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="status_id">{{ trans('admin.status') }} <strong
                                        class="text-danger">*</strong></label>
                                <select class="form-control select2" name="status_id" id="status_id">
                                    @foreach(\App\DataClasses\OrderStatusesDataClass::get() as $status)
                                        <option @if($status['id'] === $order->status_id) selected @endif value="{{ $status['id'] }}">{{ $status['name'] }}</option>
                                    @endforeach
                                </select>
                                <div class="mt-1 text-danger ajaxError" id="error-field-brand_id"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <a href="{{ route('admin.order.list.page') }}" class="btn btn-secondary">{{ trans('admin.back') }}</a>
                                    <button type="submit" class="btn btn-dark">{{ trans('admin.save') }}</button>
                                </div>
                            </div>
                            <p>
                                <strong>
                                    {{ trans('admin.order_global_details') }}
                                </strong>
                            </p>
                            <div class="mb-3">
                                <striong class="text-dark">{{ trans('admin.order_date') }}</striong>
                                <div class="mt-1">{{ $order->created_at->format('d-m-Y') }}</div>
                            </div>
                            @if ($order->recipient_type_id == \App\DataClasses\RecipientTypesDataClass::RECIPIENT_USER)
                            <div class="mb-3">
                                <striong class="text-dark">{{ trans('admin.order_person_full_name') }}</striong>
                                <div class="mt-1">{{ $order->user->first_name }} {{ $order->user->last_name }}</div>
                            </div>
                            <div class="mb-3">
                                <striong class="text-dark">{{ trans('admin.order_person_phone') }}</striong>
                                <div class="mt-1">{{ $order->user->phone }}</div>
                            </div>
                            <div class="mb-3">
                                <striong class="text-dark">{{ trans('admin.order_person_email') }}</striong>
                                <div class="mt-1">{{ $order->user->email }}</div>
                            </div>
                            @elseif($order->recipient_type_id == \App\DataClasses\RecipientTypesDataClass::RECIPIENT_CUSTOM)
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.customer_person_full_name') }}</striong>
                                    <div class="mt-1">{{ $order->user->first_name }} {{ $order->user->last_name }}</div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.customer_person_phone') }}</striong>
                                    <div class="mt-1">{{ $order->user->phone }}</div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.customer_person_email') }}</striong>
                                    <div class="mt-1">{{ $order->user->email }}</div>
                                </div>

                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.order_person_full_name') }}</striong>
                                    <div class="mt-1">{{ $order->user->first_name }} {{ $order->user->last_name }}</div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.order_person_phone') }}</striong>
                                    <div class="mt-1">{{ $order->user->phone }}</div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.order_person_email') }}</striong>
                                    <div class="mt-1">{{ $order->user->email }}</div>
                                </div>
                            @endif


                            <p>
                                <strong>
                                    {{ trans('admin.order_payment_details') }}
                                </strong>
                            </p>

                            <div class="form-group mb-3">
                                <label for="status_id">{{ trans('admin.order_payment_status') }}</label>
                                <select class="form-control select2" name="order_payment_status_id" id="order_payment_status_id">
                                    @foreach(\App\DataClasses\OrderPaymentStatusesDataClass::get() as $payment_status)
                                        <option @if($payment_status['id'] === $order->payment_status_id) selected @endif value="{{ $payment_status['id'] }}">{{ $payment_status['name'] }}</option>
                                    @endforeach
                                </select>
                                <div class="mt-1 text-danger ajaxError" id="error-field-brand_id"></div>
                            </div>

                            <div class="mb-3">
                                <striong class="text-dark">{{ trans('admin.order_payment_type') }}</striong>
                                <div class="mt-1">{{ \App\DataClasses\PaymentTypesDataClass::get($order->payment_type_id)['name'] }}</div>
                            </div>


                            <p>
                                <strong>
                                    {{ trans('admin.order_delivery_details') }}
                                </strong>
                            </p>
                            <div class="mb-3">
                                <striong class="text-dark">{{ trans('admin.order_delivery_type') }}</striong>
                                <div class="mt-1">{{ \App\DataClasses\DeliveryTypesDataClass::get($order->delivery_type_id)['name'] }}</div>
                            </div>
                            @if($order->delivery_type_id == \App\DataClasses\DeliveryTypesDataClass::ADDRESS_DELIVERY)
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.order_delivery_region') }}</striong>
                                    <div class="mt-1">{{ $order->region->name }}</div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.order_delivery_district') }}</striong>
                                    <div class="mt-1">{{ $order->district }}</div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.order_delivery_city') }}</striong>
                                    <div class="mt-1">{{ $order->city }}</div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.order_delivery_street') }}</striong>
                                    <div class="mt-1">{{ $order->street }}</div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.order_delivery_building_number') }}</striong>
                                    <div class="mt-1">{{ $order->building_number }}</div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.order_delivery_apartment_number') }}</striong>
                                    <div class="mt-1">{{ $order->apartment_number }}</div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.order_delivery_floor_number') }}</striong>
                                    <div class="mt-1">{{ $order->floor_number }}</div>
                                </div>
                                {{--                                <div class="mb-3">
                                                                    <striong class="text-dark">{{ trans('admin.order_delivery_has_elevator') }}</striong>
                                                                    <div class="mt-1">{{ $order->has_elevator ? trans('admin.yes') : trans('admin.no') }}</div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <striong class="text-dark">{{ trans('admin.order_delivery_date') }}</striong>
                                                                    <div class="mt-1">{{ $order->delivery_date }}</div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <striong class="text-dark">{{ trans('admin.order_delivery_time_idr') }}</striong>
                                                                    <div class="mt-1">{{ \App\DataClasses\DeliveryTimesDataClass::get($order->delivery_time_id)['name'] }}</div>
                                                                </div>

                                                                --}}
                            @elseif($order->delivery_type_id == \App\DataClasses\DeliveryTypesDataClass::NP_DELIVERY)
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('base.np_city') }}</striong>
                                    <div class="mt-1">{{ $order->np_city }}</div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('base.np_department') }}</striong>
                                    <div class="mt-1">{{ $order->np_department }}</div>
                                </div>
                            @elseif($order->delivery_type_id == \App\DataClasses\DeliveryTypesDataClass::SAT_DELIVERY)
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('base.np_city') }}</striong>
                                    <div class="mt-1">{{ $order->sat_city }}</div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('base.np_department') }}</striong>
                                    <div class="mt-1">{{ $order->sat_department }}</div>
                                </div>
                            @endif
                            <p>
                                <strong>
                                    {{ trans('admin.order_products') }}
                                </strong>
                            </p>
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th class="text-dark">{{ trans('admin.image') }}</th>
                                    <th class="text-dark">{{ trans('admin.attributes') }}</th>
                                    <th class="text-dark">{{ trans('admin.sku') }}</th>
                                    <th class="text-dark">{{ trans('admin.name') }}</th>
{{--                                    <th class="text-dark">{{ trans('admin.color') }}</th>--}}
                                    <th class="text-dark">{{ trans('admin.count') }}</th>
                                    <th class="text-dark">{{ trans('admin.price_per_one') }}</th>
                                    <th class="text-dark">{{ trans('admin.price') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($order->products as $product)
                                <tr>
                                    <td><a href="{{ route('store.product.page', ['productSlug' => $product->slug]) }}"><img class="order-product-image" src="{{ $product->main_image_url }}"></a></td>
                                    <td>
                                        @if($product->pivot->attributes)
                                            @php
                                                $attributes = \App\Helpers\DecodeJson::decodeJsonRecursive(json_decode($product->pivot->attributes, true));
                                                if(isset($attributes['color_id'])) {
                                                    unset($attributes['color_id']);
                                                }
                                            @endphp

                                            <div class="product-attributes">
                                                @if(is_array($attributes))
                                                    @foreach($attributes as $key => $value)
                                                        @if(is_array($value))
                                                            <div class="product-attribute-line">
                                                                <div class="attribute-value">{{ $value['name'][app()->getLocale()] }}</div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td><a href="{{ route('store.product.page', ['productSlug' => $product->slug]) }}">{{ $product->sku }}</a></td>
                                    <td><a href="{{ route('store.product.page', ['productSlug' => $product->slug]) }}">{{ $product->name }}</a></td>
                                    {{--
                                    <td>
                                        @if( !is_null($product->color) )
                                            <div class="border rounded p-1 text-center" style="background-color: {{ $product->color->hex }}; ">
                                                <span class="color-invert">{{ $product->color->name }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    --}}
                                    <td>{{ $product->pivot->count }}</td>
                                    <td>{{ round( $product->pivot->price + $product->pivot->attributes_price, 2) }}</td>
                                    <td>{{ round( ($product->pivot->price + $product->pivot->attributes_price) * $product->pivot->count, 2) }}</td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </x-admin.reactive-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript">
        $('.select2').each(function () {
            $(this).select2({
                theme: 'bootstrap4',
            });
        })
    </script>
@endpush
