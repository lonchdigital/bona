<section class="discount mb-13 mb-md-20 mt-10">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="discount-subscription position-relative overflow-hidden text-center text-md-left">
                    <img class="bg-down" src="{{ Vite::asset('resources/img/Bitmap-5.jpeg') }}" alt="Bitmap">
                    <div class="title mb-4">{{ trans('base.discount_for_email') }}</div>
                    <div class="desc mb-5">{{ trans('base.discount_for_email_explanation') }}</div>
                    <form class="discount-subscription-form">
                        <div class="form-content d-flex flex-column flex-md-row">
                            <input type="text" name="email" class="form-control mr-md-1" placeholder="{{ trans('base.enter_your_email') }}">
                            <button type="button" class="btn btn-white mt-2 mt-md-0 submit-button">{{ trans('base.send') }}</button>
                        </div>
                        <div class="error-text mt-2" style="height: 10px;"></div>
                    </form>
                    <div class="desc mb-5 email-subscription-sent-success d-none">
                        {{ trans('base.we_have_sent_letter_to_your_email') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
