@extends('layouts.store-main')

@section('content')
    <main class="main pt-5">
        <div class="content">
            <section>
                <div class="container">
                    <div class="row justify-content-md-center">
                        <div class="col-lg-6 mb-5">
                            <h2 class="mt-5 text-center">{{ trans('auth.email_confirmation_code_resend_title') }}</h2>
                            <form action="{{ route('auth.confirm-email-resend.page') }}" method="POST" class="form-content d-flex justify-content-center m-5 flex-column">
                                @csrf

                                <div class="w-full d-flex flex-column">
                                    <div class="form-group">
                                        <label class="custom-control-label2" for="email">{{ trans('auth.email') }}</label>
                                        <input class="form-control" placeholder="{{ trans('auth.email_placeholder') }}" type="text" name="email" value="{{ old('email') }}" id="email"/>
                                        @error('email')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <button class="mt-5 btn btn-outline-black" type="submit">{{ trans('auth.email_confirmation_code_resend') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@stop
