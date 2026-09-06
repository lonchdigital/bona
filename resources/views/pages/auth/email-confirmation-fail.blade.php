@extends('layouts.store-main')

@section('body_class', 'bona-auth-body')
@section('seo_title', trans('common.oops').' — '.config('app.name'))
@section('meta_description', trans('auth.email_confirmation_code_incorrect_or_expired'))

@push('head')
    <meta name="robots" content="noindex, follow">
@endpush

@section('content')
    <x-store.auth-shell
        :title="trans('common.oops')"
        :kicker="trans('auth.error_kicker')"
        :intro="trans('auth.email_confirmation_code_incorrect_or_expired')"
        :state="true"
    >
        <div class="bona-auth-state">
            <span class="bona-auth-state__icon" aria-hidden="true">
                <svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="12" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M12 12l8 8m0-8-8 8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </span>
            <div class="bona-auth-state__actions">
                <a class="bona-button bona-button--dark" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.confirm-email-resend.page') }}">{{ trans('auth.email_confirmation_code_resend') }}</a>
                <a class="bona-button bona-button--light" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}">{{ trans('common.go_to_main_page') }}</a>
            </div>
        </div>
    </x-store.auth-shell>
@endsection
