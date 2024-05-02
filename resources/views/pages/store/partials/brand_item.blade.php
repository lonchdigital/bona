<div class="item">
    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog-by-brand.page', ['brand' => $brand]) }}">
        @if(!is_null($brand->logo_image_path))
            <img src="{{$brand->logo_image_url}}" alt="Brand logo" loading="lazy">
        @else
            {{ $brand->name }}
        @endif
    </a>
</div>
