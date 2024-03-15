@extends('layouts.email-main')

@section('content')
    <!-- START CENTERED WHITE CONTAINER -->
    <table role="presentation" class="main">

        <!-- START MAIN CONTENT AREA -->
        <tr>
            <td class="wrapper">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
{{--                            <h2 class="align-center">{{ $subject }}</h2>--}}
                            <h2 class="align-center">{{ 'subject' }}</h2>
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="">
                                <tbody>
                                <tr>
                                    <td align="center">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <tbody>
                                            @foreach($order->products as $product)
                                                <tr>
                                                    <td><a href="{{ route('store.product.page', ['productSlug' => $product->slug]) }}"><img class="order-product-image" src="{{ $product->preview_image_url }}"></a></td>
                                                    <td><a href="{{ route('store.product.page', ['productSlug' => $product->slug]) }}">{{ $product->sku }}</a></td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>
                                                        @if( !is_null($product->color) )
                                                            <div class="border rounded p-1 text-center" style="background-color: {{ $product->color->hex }}; ">
                                                                <span class="color-invert">{{ $product->color->name }}</span>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>{{ $product->pivot->count }}</td>
                                                    <td>{{ $product->pivot->price }}</td>
                                                    <td>{{ round($product->pivot->count * $product->pivot->price, 2) }}</td>
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
