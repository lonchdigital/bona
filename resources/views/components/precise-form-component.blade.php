<!-- ======================== Precise Form ======================== -->
<section class="art-contact-form-section" style="background-image:url({{ asset('storage/bg-images/form-bg.png') }})">
    <div class="container">

        <header class="art-light">
            <div class="text-center">
                <h2 class="title h2">Не знаєте які двері обрати?</h2>
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

                <form action="#" method="post" class="art-contact-form">
                    @csrf
                    <p class="art-fields-row">
                        <input type="text" class="art-light-field" placeholder="{{ trans('base.name') }}">
                        <input type="text" class="art-light-field" placeholder="{{ trans('base.phone') }}">
                    </p>
                    <div class="checkbox checkbox-white agreement-line">
                        <input type="checkbox" name="agree" value="value">
                        <label for="fieldName">Даю згоду на обробку персональних даних</label>
                    </div>
                    <p><a href="#" class="btn btn-empty">{{trans('base.send')}}</a></p>
                </form>

            </div>
        </div>
    </div>
</section>
