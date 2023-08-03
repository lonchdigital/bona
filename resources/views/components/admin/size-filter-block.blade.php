<div class="row">
    <div class="col mb-5">
        <div id="field-numeric-filters">
            <div class="row">
                <div class="col-md-12">

                    <p class="mt-5">
                        <strong>
                            {{ trans('admin.product_type_'.$name.'_filter_options') }}
                        </strong>
                    </p>

                    <!-- SHOW ON MAIN FILTER -->
                    <div class="custom-control custom-checkbox mb-3">
                        <input type="hidden" name="product_size_{{ $name }}_show_on_main_filter" value="0">
                        <input class="custom-control-input" value="1" type="checkbox"
                               id="product_size_{{ $name }}_show_on_main_filter"
                               name="product_size_{{ $name }}_show_on_main_filter"
                               @if(isset($productType) && $productType['product_size_'.$name.'_show_on_main_filter']) checked @endif>
                        <label class="custom-control-label"
                               for="product_size_{{ $name }}_show_on_main_filter">{{ trans('admin.filter_show_on_main_filters_list') }}</label>
                        <div class="mt-1 text-danger ajaxError"
                             id="error-field-product_size_{{ $name }}_show_on_main_filter"></div>
                    </div>

                    <!-- FILTER NAME -->
                    <x-admin.multilanguage-input :label="trans('admin.filter_name')"
                                                 :is-required="true"
                                                 field-name="product_size_{{ $name }}_filter_name"
                                                 :values="isset($productType) ? $productType->getTranslations('product_size_' . $name . '_filter_name') : []"/>
                    <div class="form-group mb-3">
                        <label
                            for="product_size_{{ $name }}_filter_full_position_id">{{ trans('admin.filter_full_position') }}
                            <strong class="text-danger">*</strong></label>
                        <select class="form-control select2" id="product_size_{{ $name }}_filter_full_position_id"
                                name="product_size_{{ $name }}_filter_full_position_id">
                            <option value="" @if(!isset($productType)) selected
                                    @endif disabled>{{ trans('admin.select') }}  {{ mb_strtolower(trans('admin.filter_full_position'))  }}</option>
                            @foreach(\App\DataClasses\ProductFilterFullPositionOptionsDataClass::get() as $filterFullPosition)
                                <option value="{{ $filterFullPosition['id'] }}"
                                        @if(isset($productType) && $productType['product_size_'.$name.'_filter_full_position_id'] == $filterFullPosition['id']) selected @endif>{{ $filterFullPosition['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="mt-1 text-danger ajaxError"
                             id="error-field-product_size_{{ $name }}_filter_full_position_id"></div>
                    </div>

                    <!-- FILTER TYPE -->
                    <div class="form-group mb-3">
                        <label
                            for="product_size_{{ $name }}_filter_type_id">{{ trans('admin.numeric_field_filter_type_id') }}
                            <strong class="text-danger">*</strong></label>
                        <select class="form-control select2" id="product_size_{{ $name }}_filter_type_id"
                                name="product_size_{{ $name }}_filter_type_id">
                            <option value="" @if(!isset($productType)) selected
                                    @endif disabled>{{ trans('admin.select') }}  {{ mb_strtolower(trans('admin.numeric_field_filter_type_id'))  }}</option>
                            @foreach(App\DataClasses\NumericFieldFilerTypesDataClass::get() as $numericFiledFilterType)
                                <option
                                    @if(isset($productType) && $productType['product_size_'.$name.'_filter_type_id'] == $numericFiledFilterType['id']) selected
                                    @endif value="{{ $numericFiledFilterType['id'] }}">{{ $numericFiledFilterType['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="mt-1 text-danger ajaxError"
                             id="error-field-product_size_{{ $name }}_filter_type"></div>
                    </div>
                </div>
            </div>

            <!-- FILTER OPTIONS -->
            <div class="row">
                <div class="col-md-12 d-none" id="product-size-{{ $name }}-options-block">
                    <div class="row">
                        <div class="col-md-12" id="product-size-{{ $name }}-options-list">

                            <!-- EXISTING FILTER OPTIONS -->
                            @if(isset($productType) && count($productType->sizeFilterOptions->where('type', $type)))
                                @foreach($productType->sizeFilterOptions->where('type', $type) as $sizeFilterOption)
                                    <div class="row size-option pb-1"
                                         id="product-size-{{ $name }}-options-id-{{ $sizeFilterOption->id }}">
                                        <div class="col-md-12">
                                            <div class="border border-secondary rounded p-3">
                                                <!-- FILTER OPTION ID -->
                                                <input type="hidden"
                                                       name="product_size_{{ $name }}_option[{{ $sizeFilterOption->id }}][id]"
                                                       value="{{ $sizeFilterOption->id }}">

                                                <!-- FILTER OPTION NAME -->
                                                <x-admin.multilanguage-input
                                                    :is-required="true"
                                                    :label="trans('admin.numeric_filter_option_name') . ' (' . trans('admin.numeric_filter_option_name_explanation') . ')'"
                                                    field-name="product_size_{{ $name }}_option[{{ $sizeFilterOption->id }}][name]"
                                                    :values="$sizeFilterOption->getTranslations('name')"/>

                                                <!-- FILTER OPTION SLUG -->
                                                <div class="form-group mb-3">
                                                    <label
                                                        for="product-size-{{ $name }}-option-{{ $sizeFilterOption->id }}-slug">{{ trans('admin.slug') }}
                                                        <strong class="text-danger">*</strong></label>
                                                    <input type="text"
                                                           id="product-size-{{ $name }}-option-{{ $sizeFilterOption->id }}-slug"
                                                           name="product_size_{{ $name }}_option[{{ $sizeFilterOption->id }}][slug]"
                                                           class="form-control" value="{{ $sizeFilterOption->slug }}">
                                                    <div class="mt-1 text-danger ajaxError"
                                                         id="error-field-product_size_{{ $name }}_option.{{ $sizeFilterOption->id }}.slug"></div>
                                                </div>

                                                <!-- FILTER OPTION FROM -->
                                                <div class="form-group mb-3">
                                                    <label
                                                        for="product-size-{{ $name }}-option-from-{{ $sizeFilterOption->id }}">{{ trans('admin.numeric_filter_option_from') }}
                                                        <strong class="text-danger">*</strong>
                                                        ({{ trans('admin.numeric_filter_option_from_explanations') }}
                                                        )</label>
                                                    <input type="text"
                                                           id="product-size-{{ $name }}-option-from-{{ $sizeFilterOption->id }}"
                                                           name="product_size_{{ $name }}_option[{{ $sizeFilterOption->id }}][from]"
                                                           class="form-control" value="{{ $sizeFilterOption->from }}">
                                                    <div class="mt-1 text-danger ajaxError"
                                                         id="error-field-product_size_{{ $name }}_option.{{ $sizeFilterOption->id }}.from"></div>
                                                </div>

                                                <!-- FILTER OPTION TO -->
                                                <div class="form-group mb-3">
                                                    <label
                                                        for="product-size-{{ $name }}-option-to-{{ $sizeFilterOption->id }}">{{ trans('admin.numeric_filter_option_to') }}
                                                        <strong class="text-danger">*</strong>
                                                        ({{ trans('admin.numeric_filter_option_to_explanation') }}
                                                        )</label>
                                                    <input type="text"
                                                           id="product-size-{{ $name }}-option-to-{{ $sizeFilterOption->id }}"
                                                           name="product_size_{{ $name }}_option[{{ $sizeFilterOption->id }}][to]"
                                                           class="form-control" value="{{ $sizeFilterOption->to }}">
                                                    <div class="mt-1 text-danger ajaxError"
                                                         id="error-field-product_size_{{ $name }}_option.{{ $sizeFilterOption->id }}.to"></div>
                                                </div>

                                                <!-- FILTER OPTION DELETE BUTTON -->
                                                <div class="row">
                                                    <div class="col-md-12 text-center">
                                                        <a href="#" class="btn mb-2 btn-danger"
                                                           onclick="removeSizeFilterOption(event, {{ $sizeFilterOption->id }}, '{{ $name }}')">
                                                            <span class="fe fe-trash-2 fe-16 mr-2"></span>
                                                            {{ trans('admin.option_delete') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- DEFAULT FILTER OPTIONS -->
                            @else
                                <div class="row pb-1  size-option"
                                     id="product-size-{{ $name }}-options-id-{{ $firstOptionOffset }}">
                                    <div class="col-md-12">
                                        <div class="border border-secondary rounded p-3">

                                            <!-- FILTER OPTION ID -->
                                            <input type="hidden"
                                                   name="product_size_{{ $name }}_option[{{ $firstOptionOffset }}][id]"
                                                   value="{{ $firstOptionOffset }}">

                                            <!-- FILTER OPTION NAME -->
                                            <x-admin.multilanguage-input
                                                :is-required="true"
                                                :label="trans('admin.numeric_filter_option_name') . ' (' . trans('admin.numeric_filter_option_name_explanation') . ')'"
                                                field-name="product_size_{{ $name }}_option[{{ $firstOptionOffset }}][name]"
                                                :values="[]"/>

                                            <!-- FILTER OPTION SLUG -->
                                            <div class="form-group mb-3">
                                                <label
                                                    for="product-size-{{ $name }}-option-{{ $firstOptionOffset }}-slug">{{ trans('admin.slug') }}
                                                    <strong class="text-danger">*</strong></label>
                                                <input type="text"
                                                       id="product-size-{{ $name }}-option-{{ $firstOptionOffset }}-slug"
                                                       name="product_size_{{ $name }}_option[{{ $firstOptionOffset }}][slug]"
                                                       class="form-control">
                                                <div class="mt-1 text-danger ajaxError"
                                                     id="error-field-product_size_{{ $name }}_option.{{ $firstOptionOffset }}.slug"></div>
                                            </div>

                                            <!-- FILTER OPTION FROM -->
                                            <div class="form-group mb-3">
                                                <label
                                                    for="product-size-{{ $name }}-option-from-{{ $firstOptionOffset }}">{{ trans('admin.numeric_filter_option_from') }}
                                                    <strong class="text-danger">*</strong>
                                                    ({{ trans('admin.numeric_filter_option_from_explanations') }}
                                                    )</label>
                                                <input type="text"
                                                       id="product-size-{{ $name }}-option-from-{{ $firstOptionOffset }}"
                                                       name="product_size_{{ $name }}_option[{{ $firstOptionOffset }}][from]"
                                                       class="form-control">
                                                <div class="mt-1 text-danger ajaxError"
                                                     id="error-field-product_size_{{ $name }}_option.{{ $firstOptionOffset }}.from"></div>
                                            </div>

                                            <!-- FILTER OPTION TO -->
                                            <div class="form-group mb-3">
                                                <label
                                                    for="product-size-{{ $name }}-option-to-{{ $firstOptionOffset }}">{{ trans('admin.numeric_filter_option_to') }}
                                                    <strong class="text-danger">*</strong>
                                                    ({{ trans('admin.numeric_filter_option_to_explanation') }})</label>
                                                <input type="text"
                                                       id="product-size-{{ $name }}-option-to-{{ $firstOptionOffset }}"
                                                       name="product_size_{{ $name }}_option[{{ $firstOptionOffset }}][to]"
                                                       class="form-control">
                                                <div class="mt-1 text-danger ajaxError"
                                                     id="error-field-product_size_{{ $name }}_option.{{ $firstOptionOffset }}.to"></div>
                                            </div>

                                            <!-- FILTER OPTION DELETE BUTTON -->
                                            <div class="row">
                                                <div class="col-md-12 text-center">
                                                    <a href="#" class="btn mb-2 btn-danger"
                                                       onclick="removeSizeFilterOption(event, {{ $firstOptionOffset }}, '{{ $name }}')">
                                                        <span class="fe fe-trash-2 fe-16 mr-2"></span>
                                                        {{ trans('admin.option_delete') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- FILTER OPTION ADD BUTTON -->
                    <div class="row pt-3">
                        <div class="col-md-12 text-center">
                            <a href="#" id="product-size-{{ $name }}-option-add" class="btn mb-2 btn-secondary">
                                <span class="fe fe-plus-square fe-16 mr-2"></span>
                                {{ trans('admin.option_add') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@pushonce('scripts')
    <script src="/static-admin/js/cyrillic-lat-conventer.js"></script>
    <script src="/static-admin/js/jquery-helpers.js"></script>
    <script type="text/javascript">
        const TYPE_LENGTH = 'length';
        const TYPE_WIDTH = 'width';
        const TYPE_HEIGHT = 'height';

        const allTypes = [TYPE_LENGTH, TYPE_WIDTH, TYPE_HEIGHT];

        $(document).ready(function () {

            let highestSizeOptionId = 0;
            $('.size-option').each(function () {
                const id = parseInt($(this).attr('id').split('-options-id-')[1]);
                if (id >= highestSizeOptionId) {
                    highestSizeOptionId = id;
                }
            });

            allTypes.forEach(function (type) {
                const productSizeFilterTypeSelect = $(`#product_size_${type}_filter_type_id`);

                handleSelectedNumericFilterType(productSizeFilterTypeSelect.val(), type);

                //on change dropdown
                productSizeFilterTypeSelect.change(function () {
                    hideAll(type);
                    handleSelectedNumericFilterType($(this).val(), type);
                });

                //on click add option
                $(`#product-size-${type}-option-add`).click(function (event) {
                    event.preventDefault();

                    highestSizeOptionId++;
                    addSizeFilterOption(highestSizeOptionId, type);
                });

                $(`#product-size-${type}-options-list`).find('.size-option').each(function () {
                    const id = parseInt($(this).attr('id').split('-options-id-')[1]);
                    const baseElement = $(`#product_size_${type}_option\\[${id}\\]\\[name\\]_{{$baseLanguage}}`);
                    const slugElement = $(`#product-size-${type}-option-${id}-slug`);

                    addSlugHandler(baseElement, slugElement);
                });
            });

        });

        function handleSelectedNumericFilterType(value, type) {
            switch (value) {
                case '{{ App\DataClasses\NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE }}':
                    showNumericFilterOptionsType(type);
                    break;
                default:
                    break;
            }
        }

        function hideAll(type) {
            $(`#product-size-${type}-options-block`).addClass('d-none');
        }

        function showNumericFilterOptionsType(type) {
            $(`#product-size-${type}-options-block`).removeClass('d-none');
        }

        function addSizeFilterOption(id, type) {
            $(`#product-size-${type}-options-list`).append(`
                <div class="row pb-1" id="product-size-${type}-options-id-${id}">
                    <div class="col-md-12">
                        <div class="border border-secondary rounded p-3">
                            <input type="hidden" name="product_size_${type}_option[${id}][id]" value="${id}">
                            <x-admin.multilanguage-input :is-required="true" :label="trans('admin.numeric_filter_option_name') . ' (' . trans('admin.numeric_filter_option_name_explanation') . ')'" field-name="product_size_${type}_option[${id}][name]" :values="[]"/>
                            <div class="form-group mb-3">
                                <label for="product-size-${type}-option-${id}-slug">{{ trans('admin.slug') }} <strong class="text-danger">*</strong></label>
                                <input type="text" id="product-size-${type}-option-${id}-slug" name="product_size_${type}_option[${id}][slug]" class="form-control">
                                <div class="mt-1 text-danger ajaxError" id="error-field-product_size_${type}_option.${id}.slug"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="product-size-${type}-option-from-${id}">{{ trans('admin.numeric_filter_option_from') }} <strong class="text-danger">*</strong>({{ trans('admin.numeric_filter_option_from_explanations') }})</label>
                                <input type="text" id="product-size-${type}-option-from-${id}" name="product_size_${type}_option[${id}][from]" class="form-control">
                                <div class="mt-1 text-danger ajaxError" id="error-field-product_size_${type}_option.${id}.from"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="product-size-${type}-option-to-${id}">{{ trans('admin.numeric_filter_option_to') }} <strong class="text-danger">*</strong>({{ trans('admin.numeric_filter_option_to_explanation') }})</label>
                                <input type="text" id="product-size-${type}-option-to-${id}" name="product_size_${type}_option[${id}][to]" class="form-control">
                                <div class="mt-1 text-danger ajaxError" id="error-field-product_size_${type}_option.${id}.to"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <a href="#" class="btn mb-2 btn-danger" onclick="removeSizeFilterOption(event, ${id}, '${type}')">
                                        <span class="fe fe-trash-2 fe-16 mr-2"></span>
                                        {{ trans('admin.option_delete') }}
            </a>
        </div>
    </div>
</div>
</div>
</div>
`);

            const baseElement = $(`#product_size_${type}_option\\[${id}\\]\\[name\\]_{{$baseLanguage}}`);
            const slugElement = $(`#product-size-${type}-option-${id}-slug`);

            addSlugHandler(baseElement, slugElement);

            const languageCode = $('.multilang-switch.active').attr('href').substring(1);

            $(`#product-size-${type}-options-id-${id} .multilang-content`).removeClass('active').removeClass('show').each(function () {
                if ($(this).attr('language') === languageCode) {
                    $(this).addClass('active').addClass('show');
                }
            });

        }

        function removeSizeFilterOption(event, id, type) {
            event.preventDefault();
            $(`#product-size-${type}-options-id-${id}`).remove();
        }

        function addSlugHandler(baseElement, slugElement) {
            baseElement.keyup(function () {
                const CyrLat = new CyrLatConverter().init();
                const value = $(this).val();
                const latValue = CyrLat.getC2L(value);
                slugElement.val(slugify(latValue));
            });
        }


    </script>
@endpushonce
