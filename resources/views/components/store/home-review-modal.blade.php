<x-store.lead-modal
    id="dialog-home-review"
    :action="App\Helpers\MultiLangRoute::getMultiLangRoute('store.customer-review.submit')"
    form-type="review"
    :kicker="trans('base.lead_review_kicker')"
    :title="trans('base.lead_review_title')"
    :time-label="trans('base.lead_review_time')"
    :submit-label="trans('base.lead_review_submit')"
    :success-kicker="trans('base.lead_review_success_kicker')"
    :success-title="trans('base.lead_review_success_title')"
    :success-text="trans('base.lead_review_success_text')"
>
    <fieldset class="bona-lead-rating">
        <legend>{{ trans('base.lead_review_rating') }}</legend>
        <div class="bona-lead-rating__stars" data-lead-rating>
            @foreach(range(1, 5) as $rating)
                <input id="home-review-rating-{{ $rating }}" type="radio" name="rating" value="{{ $rating }}" @checked($rating === 5) required>
                <label for="home-review-rating-{{ $rating }}" aria-label="{{ trans_choice('base.review_rating', $rating, ['rating' => $rating]) }}">★</label>
            @endforeach
        </div>
    </fieldset>

    <div class="bona-lead-form__grid">
        <label class="bona-lead-field bona-lead-field--first_name">
            <span>{{ trans('base.name') }}</span>
            <input type="text" name="first_name" autocomplete="given-name" minlength="2" maxlength="60" required placeholder="{{ trans('base.lead_review_name_placeholder') }}">
        </label>
        <label class="bona-lead-field bona-lead-field--last_name">
            <span>{{ trans('base.last_name') }}</span>
            <input type="text" name="last_name" autocomplete="family-name" minlength="2" maxlength="60" required placeholder="{{ trans('base.lead_review_last_name_placeholder') }}">
        </label>
        <label class="bona-lead-field bona-lead-field--phone">
            <span>{{ trans('base.phone') }}</span>
            <input class="js-ua-phone" type="tel" name="phone" autocomplete="tel" inputmode="tel" required placeholder="+38 (0__) ___ __ __">
        </label>
        <label class="bona-lead-field bona-lead-field--email">
            <span>{{ trans('base.email') }} <small>{{ trans('base.lead_optional') }}</small></span>
            <input type="email" name="email" autocomplete="email" maxlength="191" placeholder="name@example.com">
        </label>
        <label class="bona-lead-field bona-lead-field--wide bona-lead-field--review">
            <span>{{ trans('base.product_review_text') }}</span>
            <textarea name="review" rows="4" minlength="20" maxlength="2000" required placeholder="{{ trans('base.lead_review_placeholder') }}"></textarea>
        </label>
    </div>

    <label class="bona-lead-consent bona-lead-field--agree">
        <input type="checkbox" name="agree" value="1" required>
        <span class="bona-lead-consent__box" aria-hidden="true"></span>
        <span>{{ trans('base.lead_review_consent') }}</span>
    </label>

    <label class="bona-lead-form__trap" aria-hidden="true">
        Website <input type="text" name="website" tabindex="-1" autocomplete="off">
    </label>
</x-store.lead-modal>
