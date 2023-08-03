@extends('layouts.store-main')

@section('content')
    <main class="main pt-5">
        <div class="content">
            <section>
                <div class="container">
                    <div class="row d-flex justify-content-md-center">
                        <div class="col-lg-6 mb-5 d-flex flex-column align-items-center">
                            <h2 class="mt-5 text-center">{{ trans('common.oops') }}</h2>
                            <p class="mt-5 text-center w-75">{{ trans('auth.email_confirmation_code_incorrect_or_expired') }}</p>
                            <p class="text-center">
                                <a class="m-1 btn btn btn-outline-black" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.confirm-email-resend.page') }}">{{ trans('auth.email_confirmation_code_resend') }}</a>
                                <a class="m-1 btn btn-go-to-cart" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}"><span>{{ trans('common.go_to_main_page') }}</span></a>
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@stop
