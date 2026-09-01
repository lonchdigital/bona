<div class="color-wrapper @if(\App\Services\Product\ProductFiltersService::mainColorFilterOptionChecked($filtersData, 'color', $color)) checked @endif">
    <input class="sync-input" type="checkbox"
           name="color"
           id="color-checkbox-{{$color->id}}-main"
           value="{{ $color->slug }}"
           style="display: none;"
           @if(\App\Services\Product\ProductFiltersService::mainColorFilterOptionChecked($filtersData, 'color', $color)) checked @endif>
    <label class="color-option" for="color-checkbox-{{$color->id}}-main">
        @if($color->display_as_image)
            <span
                class="link-color @if(\App\Services\Product\ProductFiltersService::mainColorFilterOptionChecked($filtersData, 'color', $color)) active @endif"
                style="background-image: url('{{$color->image_url}}');"
                aria-hidden="true"
            ><span class="before border-silver-custom"></span></span>
        @else
            <span
                class="link-color @if(\App\Services\Product\ProductFiltersService::mainColorFilterOptionChecked($filtersData, 'color', $color)) active @endif"
                @if(!$color->hex)
                    style="background: linear-gradient(90deg, rgba(255,0,0,1) 0%, rgba(255,235,0,1) 37%, rgba(5,255,0,1) 74%, rgba(59,63,250,1) 100%, rgba(0,9,255,1) 100%);"
                @else
                    style="background-color: {{$color->hex}};"
                @endif
                aria-hidden="true"
            ><span class="before border-silver-custom"></span></span>
        @endif
        <span class="color-name">{{ $color->name }}</span>
    </label>

</div>
