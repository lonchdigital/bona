<!--Price-->
<div class="filter-item filter-item--price filter-box">
    <div class="title font-title">{{ trans('base.price') }}</div>
    <div class="position-relative">
        <div id="price-slider" class="price-slider slider-range mb-3">
            <div class="currency-wrap">
                <div class="input-currency">
                    <input id="currency-first-main"
                           class="currency-first-main sync-input art-form-light-control"
                           type="number"
                           @isset($filtersData['price_from']) value="{{ $filtersData['price_from'] }}"
                           @endisset min="0" max="{{ $productsMaxPrice }}" step="1"
                           name="price_from" placeholder="0">
                </div>
                <div class="input-currency">
                    <input id="currency-last-main"
                           class="currency-last-main sync-input art-form-light-control"
                           type="number"
                           @isset($filtersData['price_to']) value="{{ $filtersData['price_to'] }}"
                           @endisset min="0" max="{{ $productsMaxPrice }}" step="1"
                           name="price_to" placeholder="{{ $productsMaxPrice }}">
                </div>
            </div>
            <div class="rangeBar-full"></div>
        </div>
        <button type="button"
                class="btn btn-empty color-dark filter-submit-main">{{ trans('base.apply') }}</button>
    </div>
</div>

@if(isset($productStatuses))
    <div class="archive-catalog-filter-left filter-box active">
        <div class="title font-title">
            {{ trans('admin.availability_status') }}
        </div>

        <div class="filter-content filter-item filter-item--type-custom position-relative checkbox-preview-wrap"> {{-- filter-item--type-custom--}}
            @foreach($productStatuses as $status)
                <div class="checkbox checkbox-preview" data-toggle="tooltip"> {{-- checkbox-preview--}}
                    <div class="custom-control custom-checkbox position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'availability_status', $status['id'])) checked @endif">
                        <input type="checkbox"
                               class="custom-control-input sync-input"
                               id="custom-field-checkbox-{{$status['id']}}-{{$status['id']}}-main"
                               name="availability_status"
                               value="{{ $status['id'] }}"
                               @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'availability_status', $status['id'])) checked @endif>
                        <label class="custom-control-label"
                               for="custom-field-checkbox-{{$status['id']}}-{{$status['id']}}-main">{{ $status['name'] }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!--Discount-->
@if(count($filters['main']))

{{--    @dd($filters['main'])--}}
    @php
        $slugToRemove = 'termin-vyrobnyctva';
        $updatedCollection = $filters['main']->reject(function ($item) use ($slugToRemove) {
            return $item->slug === $slugToRemove;
        });
    @endphp

    @foreach($updatedCollection as $filter)
        <div class="archive-catalog-filter-left filter-box active"> {{-- archive-catalog-filter-left--}}
            <div class="title font-title">
                {{ $filter->pivot->filter_name }}
            </div>

            <div class="filter-content filter-item filter-item--type-custom position-relative checkbox-preview-wrap"> {{-- filter-item--type-custom--}}
                @if($filter->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION)
                    @foreach($filter->options as $option)
                        <div class="checkbox checkbox-preview" data-toggle="tooltip"> {{-- checkbox-preview--}}

                            {{--custom-checkbox--}}
                            <div class="custom-control custom-checkbox position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $filter->slug, $option->slug)) checked @endif">
                                <input type="checkbox"
                                       class="custom-control-input sync-input"
                                       id="custom-field-checkbox-{{$filter->id}}-{{$option->id}}-main"
                                       name="{{ $filter->slug }}"
                                       value="{{ $option->slug }}"
                                       @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $filter->slug, $option->slug)) checked @endif>
                                <label class="custom-control-label"
                                       for="custom-field-checkbox-{{$filter->id}}-{{$option->id}}-main">{{ $option->name }}</label> {{--custom-control-label--}}
                            </div>
                        </div>
                    @endforeach

                @elseif($filter->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER || $filter->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE)
                    {{--                                                @dd('oh!')--}}
                @endif
            </div>
        </div> <!--/filter-box-->
    @endforeach
@endif
