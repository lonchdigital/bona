@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - HOME' }}</title>
@endsection

@section('content')

    <x-header-component :data="[
        '#' => 'delivery'
    ]" />

    <div class="common-page-section-wrapper">
        <section class="art-common-page-section">
            <div class="container">
                <div class="art-row-block art-odd">
                    @if( !empty($deliveryConfig->iframe) )
                        <div class="col-md-5 video-side">{!! $deliveryConfig->iframe !!}</div>
                    @else
                        <div class="col-md-5 image-side">
                            <img src="{{ $deliveryConfig->imageUrl }}" alt="block image">
                        </div>
                    @endif
                    <div class="col-md-7 desc-side">
                        <div class="h5 title">{{ $deliveryConfig->title }}</div>
                        {!! $deliveryConfig->description !!}
                        @if( !empty($deliveryConfig->button_url) )
                            <a href="{{ $deliveryConfig->button_url }}" target="_blank" class="btn btn-empty color-dark" >{{ $deliveryConfig->button_text }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

@stop
