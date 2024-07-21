@extends('layouts.store-main')

@section('content')

    @include('pages.store.partials.page_header', ['links' => [
    'own-2' => trans('user-profile.profile'),
    'own' => trans('user-profile.user_orders')
    ]])

    <main class="main pt-5">
        <div class="content">
            <section class="user-profile">
                <div class="container">
                    <div class="row justify-content-md-center">
                        <div class="col-12 mb-12 user-orders-wrapper">

                            @include('pages.store.partials.profile_user_navigation')


                            <div class="user-orders">

                                @foreach( $userOrders as $order )

                                    <div class="card-body">

                                    <p>
                                        <strong>
                                            {{ trans('admin.order_global_details') }}
                                        </strong>
                                    </p>
                                    <div class="mb-3">
                                        <striong class="text-dark">{{ trans('admin.order_id') }}</striong>
                                        <div class="mt-1">{{ $order->id }}</div>
                                    </div>
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
                                        <striong class="text-dark">{{ trans('admin.order_payment_status') }}</striong>
                                        <div>{{ \App\DataClasses\OrderPaymentStatusesDataClass::get($order->payment_status_id)['name'] }}</div>
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
                                    <table class="table table-hover order-items">
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
                                                <td class="product-image"><a href="{{ route('store.product.page', ['productSlug' => $product->slug]) }}"><img class="order-product-image" src="{{ $product->main_image_url }}"></a></td>
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
                                                <td>{{ $product->pivot->count }}</td>
                                                <td>{{ round( $product->pivot->price + $product->pivot->attributes_price, 2) . ' ' . $baseCurrency->name_short }}</td>
                                                <td>{{ round( ($product->pivot->price + $product->pivot->attributes_price) * $product->pivot->count, 2) . ' ' . $baseCurrency->name_short }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@stop
