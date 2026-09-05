@extends('layouts.store-main')

@section('title')
    {{-- These pages shared one title with the whole shop and were being
         indexed alongside it. Each says what it is, and none of them
         belongs in search results. --}}
    <title>{{ trans('auth.sign_in') }} | {{ config('app.name') }}</title>
    <meta name="title" content="{{ trans('auth.sign_in') }}">
    <meta name="robots" content="noindex, follow">
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['own' => trans('auth.sign_in')]])

    <main class="main">
        <div class="content">
            <section>
                <div class="container">
                    <div class="row justify-content-md-center">
                        <div class="col-lg-4 mb-5">
                            <h1 class="mt-5 text-center">{{ trans('auth.sign_in') }}</h1>
                            <form action="{{ route('auth.sign-in') }}" method="POST" class="form-content d-flex justify-content-center m-5 flex-column">
                                @csrf
                                @if(request('redirect_to') || old('redirect_to'))
                                    <input type="hidden" name="redirect_to" value="{{ old('redirect_to', request('redirect_to')) }}">
                                @endif
                                <div class="form-group">
                                    <input id="email" class="fstElement" placeholder="{{ trans('auth.email_placeholder') }}" type="text" name="email" value="{{ old('email') }}"/>
                                    @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <input id="password" class="fstElement block w-full" placeholder="{{ trans('auth.password_placeholder') }}" type="password" name="password"/>
                                    @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="custom-control custom-checkbox d-none">
                                    <input type="checkbox" class="custom-control-input" name="remember_me" id="remember_me" @if(old('remember_me')) checked @endif">
                                    <label class="custom-control-label" for="remember_me">{{ trans('auth.remember_me')  }}</label>
                                </div>

                                <button class="btn btn-empty color-dark" type="submit">{{ trans('auth.sign_in') }}</button>

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
