@extends('layouts.store-main')

@section('body_class', 'bona-auth-body')
@section('seo_title', trans('auth.email_confirmed').' — '.config('app.name'))
@section('meta_description', trans('auth.email_confirmed_thank_you'))

@push('head')
    <meta name="robots" content="noindex, follow">
@endpush

@section('content')
    <x-store.auth-shell
        :title="trans('auth.email_confirmed')"
        :kicker="trans('auth.success_kicker')"
        :intro="trans('auth.email_confirmed_thank_you')"
        :state="true"
    >
        <div class="bona-auth-state">
            <span class="bona-auth-state__icon" aria-hidden="true">
                <svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="12" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="m10.5 16.5 3.6 3.6 7.8-8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <div class="bona-auth-state__actions">
                <a class="bona-button bona-button--dark" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.sign-in.page') }}">{{ trans('auth.go_to_sign_in') }}</a>
            </div>
        </div>
    </x-store.auth-shell>
@endsection
