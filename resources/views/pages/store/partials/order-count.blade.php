<div class="info-content-add d-flex align-items-center justify-content-between flex-wrap">
    <div class="d-flex flex-wrap align-items-center no-gutters w-100">

        {{-- <a href="" class="btn btn-main art-header-coll-button" data-fancybox data-src="#order-count">{{ trans('base.order_count') }}</a> --}}

        <div id="order-count" class="art-popup-call-measurer">
            <div class="art-measurer-form-wrapper">
                <div class="container">

                    <div class="row">
                        <div class="col-12 text-center">
                            <form action="#" id="order-count-form" method="post" class="art-contact-form">
                                @csrf

                                <header class="art-light">
                                    <div class="text-center">
                                        <h2 class="title h2">{{ trans('base.order_count') }}</h2>
                                        <div class="subtitle font-two">
                                            <p class="art-form-description">
                                                {{ trans('base.call_measurer_description') }}</p>
                                        </div>
                                    </div>
                                </header>

                                <div class="art-fields-row">
                                    <div>
                                        <input type="text" class="art-light-field name-field" name="name"
                                            placeholder="{{ trans('base.name') }}">
                                    </div>
                                    <div>
                                        <input type="text" class="art-light-field phone-field" name="phone"
                                            placeholder="{{ trans('base.phone') }}">
                                    </div>
                                </div>
                                <div class="checkbox checkbox-white agreement-line agree-field">
                                    <input type="checkbox" name="agree" value="1">
                                    <label>{{ trans('base.agreement_line_start') . ' ' }}<a
                                            href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => 'dogovir-publichnoyi-oferti']) }}"
                                            class="color-white">{{ trans('base.agreement_line_end') }}</a></label>
                                </div>
                                <input type="hidden" name="event" value="submit_form_order_count">
                                <p><button type="submit" class="btn btn-empty">{{ trans('base.send') }}</button></p>

                                <a href="{{ url()->current() }}"
                                    class="d-none art-current-product-link">{{ $product->name }}</a>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
