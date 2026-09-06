@extends('layouts.store-main')

@section('body_class', 'bona-auth-body')
@section('seo_title', trans('auth.email_confirmation_code_resend_title').' — '.config('app.name'))

@push('head')
    <meta name="robots" content="noindex, follow">
@endpush

@section('content')
    <x-store.auth-shell
        :title="trans('auth.email_confirmation_code_resend_title')"
        :kicker="trans('auth.confirmation_kicker')"
        :intro="trans('auth.confirm_your_email_to_finish')"
    >
        <form action="{{ route('auth.confirm-email-resend') }}" method="POST" class="bona-auth-form">
            @csrf
            <div class="bona-auth-field">
                <label for="email">{{ trans('auth.email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ trans('auth.email_placeholder') }}" autocomplete="email" inputmode="email" required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')<p class="bona-auth-error" id="email-error">{{ $message }}</p>@enderror
            </div>
            <div class="bona-auth-form__actions">
                <button class="bona-button bona-button--dark" type="submit">{{ trans('auth.email_confirmation_code_resend') }}</button>
                <a class="bona-auth-link" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.sign-in.page') }}">{{ trans('auth.back_to_sign_in') }}</a>
            </div>
        </form>
    </x-store.auth-shell>
@endsection
