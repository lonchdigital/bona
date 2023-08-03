@extends('layouts.store-main')

@section('content')
    <main class="main pt-5">
        <div class="content">
            <section>
                <div class="container">
                    <div class="row d-flex justify-content-md-center">
                        <div class="col-lg-6 bg-light mb-5 d-flex flex-column align-items-center">
                            <h2 class="mt-5 text-center">{{ trans('common.oops') }}</h2>
                            <p class="mt-5 text-center w-75">{{ $message }}</p>
                            <p class="text-center">
                                <a class="btn btn-outline-black" href="{{ route('store.home') }}">{{ trans('common.go_to_main_page') }}</a>
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@stop
