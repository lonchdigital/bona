<div class="item">
    <a href="{{ app(App\Services\Brand\BrandCatalogUrlService::class)->storefrontUrl($brand) }}">
        @if(!is_null($brand->logo_image_path))
            <img src="{{$brand->logo_image_url}}" alt="{{ $brand->name }}" loading="lazy">
        @else
            {{ $brand->name }}
        @endif
    </a>
</div>
