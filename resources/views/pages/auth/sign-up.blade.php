@extends('layouts.store-main')

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['own' => trans('auth.sign_up_title')]])

    <main class="main pt-5">
        <div class="content">
            <section>
                <div class="container">
                    <div class="row justify-content-md-center">
                        <div class="col-lg-6 mb-5">
                            <h2 class="mt-5 text-center">{{ trans('auth.sign_up_title') }}</h2>
                                <form action="{{ route('auth.sign-up') }}" method="POST" class="form-content d-flex justify-content-center m-5 flex-column">
                                    @csrf

                                    <div class="w-full d-flex justify-content-lg-around flex-column flex-lg-row">
                                        <div class="form-group lg-w-45">
                                            <label class="custom-control-label2" for="first_name">{{ trans('auth.first_name') }}</label>
                                            <input class="form-control" placeholder="{{ trans('auth.first_name_placeholder') }}" type="text" name="first_name" value="{{ old('first_name') }}"/>
                                            @error('first_name')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group lg-w-45">
                                            <label class="custom-control-label2" for="last_name">{{ trans('auth.last_name') }}</label>
                                            <input class="form-control" placeholder="{{ trans('auth.last_name_placeholder') }}" type="text" name="last_name" value="{{ old('last_name') }}"/>
                                            @error('last_name')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="w-full d-flex justify-content-lg-around flex-column flex-lg-row">
                                        <div class="form-group lg-w-45">
                                            <label class="custom-control-label2" for="email">{{ trans('auth.email') }}</label>
                                            <input id="email" class="form-control" placeholder="{{ trans('auth.email_placeholder') }}" type="text" name="email" value="{{ old('email') }}"/>
                                            @error('email')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group lg-w-45">
                                            <label class="custom-control-label2" for="phone">{{ trans('auth.phone') }}</label>
                                            <input id="phone" class="form-control" placeholder="{{ trans('auth.phone_placeholder') }}" type="text" name="phone" value="{{ old('phone') }}"/>
                                            @error('phone')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="w-full d-flex justify-content-lg-around flex-column flex-lg-row">
                                        <div class="form-group lg-w-45">
                                            <label class="custom-control-label2" for="password">{{ trans('auth.password') }}</label>
                                            <div class="d-flex flex-row align-items-center justify-content-end password-input">
                                                <input id="password" class="form-control block w-full" type="password" name="password"/>

                                            </div>

                                            @error('password')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group lg-w-45">
                                            <label class="custom-control-label2" for="password_confirmation">{{ trans('auth.password_confirmation') }}</label>
                                            <div class="d-flex flex-row align-items-center justify-content-end password-input">
                                                <input id="password_confirmation" class="form-control block w-full" type="password" name="password_confirmation"/>

                                            </div>
                                            @error('password_confirmation')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <button class="mt-5 btn btn-outline-black" type="submit">{{ trans('auth.sign_up') }}</button>
                                </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@stop
