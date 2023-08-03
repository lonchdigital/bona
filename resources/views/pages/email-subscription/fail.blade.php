@extends('layouts.store-main')

@section('content')
    <main class="main pt-5">
        <div class="content">
            <section>
                <div class="container">
                    <div class="row d-flex justify-content-md-center">
                        <div class="col-lg-6 mb-5 d-flex flex-column align-items-center">
                            <h2 class="mt-5 text-center">{{ trans('base.email_confirmed_fail') }}</h2>
                            <p class="mt-5 text-center w-75">{{ $errorMessage }}</p>
                            <p class="text-center">
                                <a class="btn btn-outline-black" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.cart.page') }}">{{ trans('base.go_to_cart') }}</a>
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@stop
