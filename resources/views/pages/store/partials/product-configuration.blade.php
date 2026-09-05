@php
    $configuration = \App\Support\Commerce\ProductConfiguration::for($product, $product->pivot->attributes);
    $configurationClass = $class ?? 'bona-order-product-config';
@endphp

@if($configuration)
    <div class="{{ $configurationClass }}">
        @foreach($configuration as $item)
            <span class="bona-order-product-config__item">
                @if($item['swatch'])
                    <i class="bona-order-product-config__swatch" style="background-color: {{ $item['swatch'] }}" aria-hidden="true"></i>
                @endif
                @if($item['name'])<b>{{ $item['name'] }}:</b>@endif
                <span>{{ $item['label'] }}</span>
            </span>
        @endforeach
    </div>
@endif
