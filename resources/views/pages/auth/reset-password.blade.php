@extends('layouts.store-main')

@section('body_class', 'bona-auth-body')
@section('seo_title', trans('auth.set_new_password').' — '.config('app.name'))

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    <x-store.auth-shell
        :title="trans('auth.set_new_password')"
        :kicker="trans('auth.recovery_kicker')"
        :intro="trans('auth.reset_password_text')"
    >
        <form action="{{ route('auth.reset-password') }}" method="POST" class="bona-auth-form">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="bona-auth-field">
                <label for="email">{{ trans('auth.email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')<p class="bona-auth-error" id="email-error">{{ $message }}</p>@enderror
            </div>
            <div class="bona-auth-field">
                <label for="password">{{ trans('auth.password') }}</label>
                <input id="password" type="password" name="password" autocomplete="new-password" required @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                @error('password')<p class="bona-auth-error" id="password-error">{{ $message }}</p>@enderror
            </div>
            <div class="bona-auth-field">
                <label for="password_confirmation">{{ trans('auth.password_confirmation') }}</label>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
            </div>
            <div class="bona-auth-form__actions">
                <button class="bona-button bona-button--dark" type="submit">{{ trans('auth.save_new_password') }}</button>
            </div>
        </form>
    </x-store.auth-shell>
@endsection
