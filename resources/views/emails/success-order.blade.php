@extends('layouts.email-product-list')

@section('content')
    <!-- START CENTERED WHITE CONTAINER -->
    <table role="presentation" class="main">

        <!-- START MAIN CONTENT AREA -->
        <tr>
            <td class="wrapper">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <h2 class="align-center">{{ trans('emails.your_order') . ' № ' . $order->id }}</h2>
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="">
                                <tbody>
                                <tr>
                                    <td align="center">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <thead>
                                            <tr>
                                                <th>{{ trans('emails.table_image') }}</th>
                                                <th>{{ trans('emails.table_product_name') }}</th>
                                                <th>{{ trans('emails.table_attributes') }}</th>
                                                <th>{{ trans('emails.table_sku') }}</th>
                                                <th>{{ trans('emails.table_product_count') }}</th>
                                                <th>{{ trans('emails.table_product_single_price') }}</th>
                                                <th>{{ trans('emails.table_product_total_price') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody class="art-product-list">
                                            @foreach($order->products as $product)
                                                <tr class="art-product-row">
                                                    <td class="art-column-img">
                                                        <a href="{{ route('store.product.page', ['productSlug' => $product->slug]) }}">
                                                            <img class="order-product-image" src="{{ $product->preview_image_full_url }}" alt="Product image">
                                                        </a>
                                                    </td>
                                                    <td><a href="{{ route('store.product.page', ['productSlug' => $product->slug]) }}">{{ $product->name }}</a></td>
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
                                                    <td>{{ $product->pivot->count }}</td>
                                                    <td>{{ round( $product->pivot->price + $product->pivot->attributes_price, 2) }}</td>
                                                    <td>{{ round( ($product->pivot->price + $product->pivot->attributes_price) * $product->pivot->count, 2) }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- END MAIN CONTENT AREA -->
    </table>
    <!-- END CENTERED WHITE CONTAINER -->
@endsection
