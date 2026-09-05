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
                                            @foreach($orderProductGroups as $group)
                                                @if($group['is_bundle'])
                                                    <tr><td colspan="7" style="padding:12px 10px;background:#f3eee5;border-top:2px solid #ad8758;text-align:left"><strong style="display:block;color:#846238;font-size:11px;text-transform:uppercase">{{ trans('base.cart_bundle_label') }}</strong><span style="display:block;margin-top:3px">{{ $group['parent']->name }}</span></td></tr>
                                                @endif
                                                @foreach(collect([$group['parent']])->concat($group['items']) as $product)
                                                    @php
                                                        $isBundleItem = $group['is_bundle'] && $product->pivot->bundle_role === \App\Support\Commerce\ProductBundle::ROLE_ITEM;
                                                        $configuration = \App\Support\Commerce\ProductConfiguration::for($product, $product->pivot->attributes);
                                                        $bundleCategory = $isBundleItem ? \App\Support\Commerce\ProductBundle::localizedCategory($product->pivot->bundle_category) : '';
                                                        $imageUrl = $product->pivot->current_image_path
                                                            ? url('/storage/'.$product->pivot->current_image_path)
                                                            : $product->preview_image_full_url;
                                                    @endphp
                                                    <tr class="art-product-row" @if($isBundleItem) style="background:#faf8f4" @endif>
                                                        <td class="art-column-img" @if($isBundleItem) style="padding-left:20px" @endif>
                                                            <a href="{{ route('store.product.page', ['productSlug' => $product->slug]) }}">
                                                                <img class="order-product-image" src="{{ $imageUrl }}" alt="{{ $product->name }}" style="background:#fff;object-fit:contain">
                                                            </a>
                                                        </td>
                                                        <td class="art-column-name">
                                                            @if($bundleCategory)<small style="display:block;color:#846238;font-size:9px;text-transform:uppercase">{{ $bundleCategory }}</small>@endif
                                                            <a href="{{ route('store.product.page', ['productSlug' => $product->slug]) }}">{{ $product->name }}</a>
                                                        </td>
                                                        <td>
                                                            @foreach($configuration as $item)
                                                                <div style="margin-bottom:4px;font-size:11px">
                                                                    @if($item['swatch'])<i style="display:inline-block;width:11px;height:11px;margin-right:3px;border:1px solid #bdb5a9;border-radius:50%;background:{{ $item['swatch'] }};vertical-align:middle"></i>@endif
                                                                    @if($item['name'])<strong>{{ $item['name'] }}:</strong>@endif {{ $item['label'] }}
                                                                </div>
                                                            @endforeach
                                                        </td>
                                                        <td><a href="{{ route('store.product.page', ['productSlug' => $product->slug]) }}">{{ $product->sku }}</a></td>
                                                        <td>{{ $product->pivot->count }}</td>
                                                        <td>{{ round($product->pivot->price + $product->pivot->attributes_price, 2) }}</td>
                                                        <td>{{ round(($product->pivot->price + $product->pivot->attributes_price) * $product->pivot->count, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                            </tbody>
                                        </table>
                                        @php
                                            $summary = app(\App\Services\Pricing\PricingService::class)->forOrder($order);
                                            $currency = app(\App\Services\Currency\CurrencyService::class)->getBaseCurrency()->name_short;
                                            $emailPrice = fn ($amount) => number_format((float) $amount, 2, ',', ' ').' '.$currency;
                                            $emailRate = rtrim(rtrim(number_format((float) $summary['installment_rate'], 2, ',', ''), '0'), ',');
                                        @endphp
                                        <table role="presentation" border="0" cellpadding="8" cellspacing="0" style="max-width: 520px; margin: 28px 0 0 auto;">
                                            <tbody>
                                                <tr><td style="text-align:left">{{ trans('base.products_price') }}</td><td style="text-align:right"><strong>{{ $emailPrice($summary['products']) }}</strong></td></tr>
                                                @if($summary['discount'] > 0)
                                                    <tr><td style="text-align:left">{{ trans('base.products_price_discount') }}</td><td style="text-align:right"><strong>−{{ $emailPrice($summary['discount']) }}</strong></td></tr>
                                                @endif
                                                <tr><td style="text-align:left">{{ trans('base.delivery') }}</td><td style="text-align:right"><strong>{{ $emailPrice($summary['delivery']) }}</strong></td></tr>
                                                @if($summary['installment_fee'] > 0)
                                                    <tr><td style="text-align:left">{{ trans('base.installment_surcharge') }} (+{{ $emailRate }}%)</td><td style="text-align:right"><strong>{{ $emailPrice($summary['installment_fee']) }}</strong></td></tr>
                                                    <tr><td style="text-align:left">{{ trans('base.checkout_payment_period_label') }}</td><td style="text-align:right"><strong>{{ $summary['installment_period'] }}</strong></td></tr>
                                                @endif
                                                <tr><td style="text-align:left; border-top:1px solid #ddd"><strong>{{ trans('base.products_price_total') }}</strong></td><td style="text-align:right; border-top:1px solid #ddd"><strong>{{ $emailPrice($summary['total']) }}</strong></td></tr>
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
