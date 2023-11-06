@extends('layouts.store-main')

@section('content')
    <main class="main pt-5">
        <div class="content">
            <section>
                <div class="container">
                    <div class="row justify-content-md-center">
                        <div class="col-lg-4 mb-5">
                            <h2 class="mt-5 text-center">{{ trans('auth.sign_in') }}</h2>
                            <form action="{{ route('auth.sign-in') }}" method="POST" class="form-content d-flex justify-content-center m-5 flex-column">
                                @csrf
                                <div class="form-group">
                                    <label class="custom-control-label2" for="email">{{ trans('auth.email') }}</label>
                                    <input id="email" class="form-control" placeholder="{{ trans('auth.email_placeholder') }}" type="text" name="email" value="{{ old('email') }}"/>
                                    @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror

                                    <label class="custom-control-label2" for="password">{{ trans('auth.password') }}</label>
                                    <div class="d-flex flex-row align-items-center justify-content-end password-input">
                                        <input id="password" class="form-control block w-full" placeholder="{{ trans('auth.password_placeholder') }}" type="password" name="password"/>

                                    </div>
                                </div>
                                @error('password')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror

                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="remember_me" id="remember_me" @if(old('remember_me')) checked @endif">
                                    <label class="custom-control-label" for="remember_me">{{ trans('auth.remember_me')  }}</label>
                                </div>

                                <button class="btn btn-outline-black" type="submit">{{ trans('auth.sign_in') }}</button>

                                <div class="form-group pt-2 mb-0 d-flex justify-content-between">
                                    <a class="link-sort" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.forgot-password.page') }}">{{ trans('auth.forgot_password') }}</a>
                                    <a class="link-sort" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.sign-up') }}">{{ trans('auth.go_to_sign_up') }}</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@stop
