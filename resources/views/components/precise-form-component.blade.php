<!-- ======================== Precise Form ======================== -->
<section class="art-contact-form-section" @if(array_key_exists('formImage', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['formImage'])) style="background-image:url({{ '/storage/' . $applicationGlobalOptions['formImage'] }})" @endif>
    <div class="container">

        <header class="art-light">
            <div class="text-center">
                @if(array_key_exists('formTitle', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['formTitle']))
                    <h2 class="title h2">{{ $applicationGlobalOptions['formTitle'][app()->getLocale()] }}</h2>
                @endif
                <div class="subtitle font-two">
                    @if(array_key_exists('formText', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['formText']))
                        <p class="art-form-description">{{ $applicationGlobalOptions['formText'][app()->getLocale()] }}</p>
                    @endif
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
                    <div class="form-errors"></div>
                </form>

            </div>
        </div>
    </div>
</section>
