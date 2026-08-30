@props([
    'options' => [],
])

<div id="dialog-call-consultation" class="art-popup-call-measurer" style="display: none">
    <div class="art-measurer-form-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <form action="#" id="user-call-master" method="post" class="art-contact-form">
                        @csrf
                        <header class="art-light">
                            <div class="text-center">
                                <h2 class="title h2">{{ data_get($options, 'formTitle.'.app()->getLocale(), trans('base.call_measurer')) }}</h2>
                                <div class="subtitle font-two">
                                    <p class="art-form-description">{{ data_get($options, 'formText.'.app()->getLocale(), trans('base.call_measurer_description')) }}</p>
                                </div>
                            </div>
                        </header>
                        <div class="art-fields-row">
                            <div><input type="text" class="art-light-field name-field" name="name" placeholder="{{ trans('base.name') }}"></div>
                            <div><input type="text" class="art-light-field phone-field" name="phone" placeholder="{{ trans('base.phone') }}"></div>
                        </div>
                        <div class="art-fields-row">
                            <div class="art-solid-field">
                                <textarea class="art-light-field" name="description" placeholder="{{ trans('base.your_message') }}"></textarea>
                            </div>
                        </div>
                        <div class="checkbox checkbox-white agreement-line agree-field">
                            <input type="checkbox" name="agree" value="1">
                            <label>
                                {{ trans('base.agreement_line_start').' ' }}
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => 'dogovir-publichnoyi-oferti']) }}" class="color-white">
                                    {{ trans('base.agreement_line_end') }}
                                </a>
                            </label>
                        </div>
                        <input type="hidden" name="event" value="submit_form_home_slider">
                        <p><button type="submit" class="btn btn-empty">{{ trans('base.send') }}</button></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
