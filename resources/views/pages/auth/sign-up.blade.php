@extends('layouts.store-main')

@section('body_class', 'bona-auth-body')
@section('seo_title', trans('auth.sign_up_title').' — '.config('app.name'))
@section('meta_description', trans('auth.sign_up_intro'))

@push('head')
    <meta name="robots" content="noindex, follow">
@endpush

@section('content')
    <x-store.auth-shell
        :title="trans('auth.sign_up_title')"
        :kicker="trans('auth.account_kicker')"
        :intro="trans('auth.sign_up_intro')"
    >
        <form action="{{ route('auth.sign-up') }}" method="POST" class="bona-auth-form">
            @csrf

            <div class="bona-auth-form__grid">
                <div class="bona-auth-field">
                    <label for="first_name">{{ trans('auth.first_name') }}</label>
                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" placeholder="{{ trans('auth.first_name_placeholder') }}" autocomplete="given-name" required @error('first_name') aria-invalid="true" aria-describedby="first-name-error" @enderror>
                    @error('first_name')<p class="bona-auth-error" id="first-name-error">{{ $message }}</p>@enderror
                </div>
                <div class="bona-auth-field">
                    <label for="last_name">{{ trans('auth.last_name') }}</label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" placeholder="{{ trans('auth.last_name_placeholder') }}" autocomplete="family-name" required @error('last_name') aria-invalid="true" aria-describedby="last-name-error" @enderror>
                    @error('last_name')<p class="bona-auth-error" id="last-name-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="bona-auth-form__grid">
                <div class="bona-auth-field">
                    <label for="email">{{ trans('auth.email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ trans('auth.email_placeholder') }}" autocomplete="email" inputmode="email" required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email')<p class="bona-auth-error" id="email-error">{{ $message }}</p>@enderror
                </div>
                <div class="bona-auth-field">
                    <label for="phone">{{ trans('auth.phone') }}</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="{{ trans('auth.phone_placeholder') }}" autocomplete="tel" inputmode="tel" required @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
                    @error('phone')<p class="bona-auth-error" id="phone-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="bona-auth-form__grid">
                <div class="bona-auth-field">
                    <label for="password">{{ trans('auth.password') }}</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" required @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                    @error('password')<p class="bona-auth-error" id="password-error">{{ $message }}</p>@enderror
                </div>
                <div class="bona-auth-field">
                    <label for="password_confirmation">{{ trans('auth.password_confirmation') }}</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required @error('password_confirmation') aria-invalid="true" aria-describedby="password-confirmation-error" @enderror>
                    @error('password_confirmation')<p class="bona-auth-error" id="password-confirmation-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="bona-auth-form__actions">
                <button class="bona-button bona-button--dark" type="submit">{{ trans('auth.sign_up') }}</button>
                <span>{{ trans('auth.already_registered') }}</span>
                <a class="bona-auth-link" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.sign-in.page') }}">
                    {{ trans('auth.sign_in') }}
                </a>
            </div>
        </form>
    </x-store.auth-shell>
@endsection
