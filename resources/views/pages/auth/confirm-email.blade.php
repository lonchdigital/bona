@extends('layouts.store-main')

@section('title')
    {{-- These pages shared one title with the whole shop and were being
         indexed alongside it. Each says what it is, and none of them
         belongs in search results. --}}
    <title>{{ trans('auth.confirm_your_email') }} | {{ config('app.name') }}</title>
    <meta name="title" content="{{ trans('auth.confirm_your_email') }}">
    <meta name="robots" content="noindex, follow">
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['own' => trans('auth.confirm_your_email')]])

    <main class="main pt-5">
        <div class="content">
            <section>
                <div class="container">
                    <div class="row d-flex justify-content-md-center">
                        <div class="col-lg-6 mb-5 d-flex flex-column align-items-center">
                            <h1 class="mt-5 text-center">{{ trans('auth.confirm_your_email') }}</h1>
                            <p class="mt-5 text-center w-75">{{ trans('auth.confirm_your_email_to_finish') }}</p>
                            <p class="text-center mt-4">
                                <a class="btn btn-main" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.sign-in.page') }}">{{ trans('auth.go_to_sign_in') }}</a>
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@stop
