@extends('layouts.store-main')

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['own' => trans('auth.reset_password_success_title')]])

    <main class="main pt-5">
        <div class="content">
            <section>
                <div class="container">
                    <div class="row d-flex justify-content-md-center">
                        <div class="col-lg-6 mb-5 d-flex flex-column align-items-center">
                            <h2 class="mt-5 text-center">{{ trans('auth.reset_password_success_title') }}</h2>
                            <p class="mt-5 text-center w-75">{{ trans('auth.reset_password_success_text') }}</p>
                            <p class="text-center">
                                <a class="btn btn-main" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.sign-in.page') }}">{{ trans('auth.go_to_sign_in') }}</a>
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@stop
