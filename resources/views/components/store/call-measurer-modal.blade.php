<x-store.lead-modal
    id="dialog-call-measurer"
    :action="App\Helpers\MultiLangRoute::getMultiLangRoute('store.choose.doors')"
    form-type="measure"
    :kicker="trans('base.lead_measurer_kicker')"
    :title="trans('base.lead_measurer_title')"
    :time-label="trans('base.lead_measurer_time')"
    :intro="trans('base.lead_measurer_intro')"
    :submit-label="trans('base.lead_measurer_submit')"
    :success-kicker="trans('base.lead_measurer_success_kicker')"
    :success-title="trans('base.lead_measurer_success_title')"
    :success-text="trans('base.lead_measurer_success_text')"
>
    <input type="hidden" name="title" value="{{ trans('base.call_measurer') }}">
    <input type="hidden" name="event" value="submit_form_call_master">

    <div class="bona-lead-form__grid">
        <label class="bona-lead-field bona-lead-field--name">
            <span>{{ trans('base.name') }}</span>
            <input type="text" name="name" autocomplete="name" minlength="2" maxlength="120" required placeholder="{{ trans('base.lead_name_placeholder') }}">
        </label>
        <label class="bona-lead-field bona-lead-field--phone">
            <span>{{ trans('base.phone') }}</span>
            <input class="js-ua-phone" type="tel" name="phone" autocomplete="tel" inputmode="tel" required placeholder="+38 (0__) ___ __ __">
        </label>
        <label class="bona-lead-field bona-lead-field--wide bona-lead-field--description">
            <span>{{ trans('base.your_message') }}</span>
            <textarea name="description" rows="3" maxlength="2000" placeholder="{{ trans('base.lead_measurer_message_placeholder') }}"></textarea>
        </label>
    </div>

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
