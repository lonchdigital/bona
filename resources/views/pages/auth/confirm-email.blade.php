@extends('layouts.store-main')

@section('body_class', 'bona-auth-body')
@section('seo_title', trans('auth.confirm_your_email').' — '.config('app.name'))
@section('meta_description', trans('auth.confirm_your_email_to_finish'))

@push('head')
    <meta name="robots" content="noindex, follow">
@endpush

@section('content')
    <x-store.auth-shell
        :title="trans('auth.confirm_your_email')"
        :kicker="trans('auth.confirmation_kicker')"
        :intro="trans('auth.confirm_your_email_to_finish')"
        :state="true"
    >
        <div class="bona-auth-state">
            <span class="bona-auth-state__icon" aria-hidden="true">
                <svg viewBox="0 0 32 32"><path d="M4.5 8.5h23v15h-23zM5 9l11 8 11-8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            </span>
            <div class="bona-auth-state__actions">
                <a class="bona-button bona-button--dark" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.sign-in.page') }}">{{ trans('auth.go_to_sign_in') }}</a>
            </div>
        </div>
    </x-store.auth-shell>
@endsection
