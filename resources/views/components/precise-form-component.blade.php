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
                    <div class="art-fields-row">
                        <div>
                            <input type="text" class="art-light-field name-field" name="name" placeholder="{{ trans('base.name') }}">
                        </div>
                        <div>
                            <input type="text" class="art-light-field phone-field" name="phone" placeholder="{{ trans('base.phone') }}">
                        </div>
                    </div>
                    <div class="checkbox checkbox-white agreement-line agree-field">
                        <input type="checkbox" name="agree" value="1">
                        <label for="fieldName">{{ trans('base.agreement_line_start') . ' ' . trans('base.agreement_line_end') }}</label>
                    </div>
                    <input type="hidden" name="event" value="submit_form_choose_doors">
                    <p><button type="submit" class="btn btn-empty">{{ trans('base.send') }}</button></p>
                </form>

            </div>
        </div>
    </div>
</section>
