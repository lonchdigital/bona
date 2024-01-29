<!-- ======================== Precise Form ======================== -->
<section class="art-contact-form-section" style="background-image:url({{ asset('storage/bg-images/form-bg.png') }})">
    <div class="container">

        <header class="art-light">
            <div class="text-center">
                <h2 class="title h2">{{ trans('base.want_choose_door') }}</h2>
                <div class="subtitle font-two">
                    <p class="art-form-description">
                        We believe in creativity as one of the major forces of progress. With this idea, we traveled throughout Italy
                        to find exceptional artisans and bring their unique handcrafted objects to connoisseurs everywhere.
                    </p>
                </div>
            </div>
        </header>

        <div class="row">
            <div class="col-12 text-center">

                <form action="#" id="user-choose-doors" method="post" class="art-contact-form">
                    @csrf
                    <p class="art-fields-row">
                        <input type="text" class="art-light-field" name="name" placeholder="{{ trans('base.name') }}">
                        <input type="text" class="art-light-field" name="phone" placeholder="{{ trans('base.phone') }}">
                    </p>
                    <div class="checkbox checkbox-white agreement-line">
                        <input type="checkbox" name="agree" value="1">
                        <label for="fieldName">{{ trans('base.agreement_line_start') . ' ' . trans('base.agreement_line_end') }}</label>
                    </div>
                    <p><button type="submit" class="btn btn-empty">{{ trans('base.send') }}</button></p>
                    <div class="choose-doors-errors"></div>
                </form>

            </div>
        </div>
    </div>
</section>
