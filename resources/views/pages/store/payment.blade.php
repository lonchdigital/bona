@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - HOME' }}</title>
@endsection

@section('content')
    <main class="main">
        <div class="content">
            <section class="product-collection-slider">
                <div class="container">
                    <div class="row">
                        <div class="col text-center">
                            <div class="head mb-4 mt-6">
                                {{ trans('base.payment') }}
                            </div>
                        </div>
                    </div>
                    <div class="row mb-10">
                        <div class="col-12 d-flex justify-content-center">
                            {!! $form !!}
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@stop
