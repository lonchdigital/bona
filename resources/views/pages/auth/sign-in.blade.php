@extends('layouts.store-main')

@section('body_class', 'bona-auth-body')
@section('seo_title', trans('auth.sign_in_title').' — '.config('app.name'))
@section('meta_description', trans('auth.sign_in_intro'))

@push('head')
    <meta name="robots" content="noindex, follow">
@endpush

@section('content')
    <x-store.auth-shell
        :title="trans('auth.sign_in_title')"
        :kicker="trans('auth.account_kicker')"
        :intro="trans('auth.sign_in_intro')"
    >
        <form action="{{ route('auth.sign-in') }}" method="POST" class="bona-auth-form">
            @csrf
            @if(request('redirect_to') || old('redirect_to'))
                <input type="hidden" name="redirect_to" value="{{ old('redirect_to', request('redirect_to')) }}">
            @endif

            @if(session('success'))
                <p class="bona-auth-notice" role="status">{{ session('success') }}</p>
            @endif

            <div class="bona-auth-field">
                <label for="email">{{ trans('auth.email') }}</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="{{ trans('auth.email_placeholder') }}"
                    autocomplete="email"
                    inputmode="email"
                    required
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                >
                @error('email')<p class="bona-auth-error" id="email-error">{{ $message }}</p>@enderror
            </div>

            <div class="bona-auth-field">
                <label for="password">{{ trans('auth.password') }}</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="{{ trans('auth.password_placeholder') }}"
                    autocomplete="current-password"
                    required
                    @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                >
                @error('password')<p class="bona-auth-error" id="password-error">{{ $message }}</p>@enderror
            </div>

            <div class="bona-auth-form__row">
                <label class="bona-auth-check" for="remember_me">
                    <input type="checkbox" name="remember_me" id="remember_me" value="1" @checked(old('remember_me'))>
                    <span>{{ trans('auth.remember_me') }}</span>
                </label>
                <a class="bona-auth-link" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.forgot-password.page') }}">
                    {{ trans('auth.forgot_password') }}
                </a>
            </div>

            <div class="bona-auth-form__actions">
                <button class="bona-button bona-button--dark" type="submit">{{ trans('auth.sign_in') }}</button>
                <span>{{ trans('auth.not_registered') }}</span>
                <a class="bona-auth-link" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.sign-up.page') }}">
                    {{ trans('auth.go_to_sign_up') }}
                </a>
            </div>
        </form>
    </x-store.auth-shell>
@endsection
