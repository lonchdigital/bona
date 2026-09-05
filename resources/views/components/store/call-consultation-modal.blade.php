@props([
    'options' => [],
])

@php
    $title = data_get($options, 'formTitle.'.app()->getLocale()) ?: trans('base.lead_consultation_title');
    $intro = data_get($options, 'formText.'.app()->getLocale()) ?: trans('base.lead_consultation_intro');
@endphp

<x-store.lead-modal
    id="dialog-call-consultation"
    :action="App\Helpers\MultiLangRoute::getMultiLangRoute('store.choose.doors')"
    form-type="selection"
    :kicker="trans('base.lead_consultation_kicker')"
    :title="$title"
    :time-label="trans('base.lead_consultation_time')"
    :intro="$intro"
    :submit-label="trans('base.lead_consultation_submit')"
    :success-kicker="trans('base.lead_consultation_success_kicker')"
    :success-title="trans('base.lead_consultation_success_title')"
    :success-text="trans('base.lead_consultation_success_text')"
    :wide="true"
>
    <input type="hidden" name="title" value="{{ $title }}">
    <input type="hidden" name="description" value="">
    <input type="hidden" name="event" value="submit_form_home_slider">

    <div class="bona-lead-form__grid">
        <label class="bona-lead-field bona-lead-field--name">
            <span>{{ trans('base.name') }}</span>
            <input type="text" name="name" autocomplete="name" minlength="2" maxlength="120" required placeholder="{{ trans('base.lead_name_placeholder') }}">
        </label>
        <label class="bona-lead-field bona-lead-field--phone">
            <span>{{ trans('base.phone') }}</span>
            <input class="js-ua-phone" type="tel" name="phone" autocomplete="tel" inputmode="tel" required placeholder="+38 (0__) ___ __ __">
        </label>
    </div>

    <fieldset class="bona-lead-fieldset" data-lead-choice-group>
        <legend>{{ trans('base.lead_consultation_door_type') }}</legend>
        <div class="bona-lead-choice">
            <label><input type="radio" name="door_type" value="interior" checked><span>{{ trans('base.lead_door_interior') }}</span></label>
            <label><input type="radio" name="door_type" value="entrance"><span>{{ trans('base.lead_door_entrance') }}</span></label>
            <label><input type="radio" name="door_type" value="both"><span>{{ trans('base.lead_door_both') }}</span></label>
        </div>
    </fieldset>

    <label class="bona-lead-consent bona-lead-field--agree">
        <input type="checkbox" name="agree" value="1" required>
        <span class="bona-lead-consent__box" aria-hidden="true"></span>
        <span>
            {{ trans('base.agreement_line_start') }}
            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => 'dogovir-publichnoyi-oferti']) }}">
                {{ trans('base.agreement_line_end') }}
            </a>
        </span>
    </label>

    <label class="bona-lead-form__trap" aria-hidden="true">
        Website <input type="text" name="website" tabindex="-1" autocomplete="off">
    </label>
</x-store.lead-modal>
