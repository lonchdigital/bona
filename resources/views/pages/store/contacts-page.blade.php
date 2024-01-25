@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - HOME' }}</title>
@endsection

@section('content')

    <x-header-component :data="[
        '#' => 'contacts'
    ]" />

    <section class="art-contacts-page-section art-section-pd">
        <div class="container">

            <h1>{{ trans('base.contacts') }}</h1>

            @if( !is_null($contactsConfig) )

                <div class="art-contacts-line">
                    <div class="art-contacts-left">

                        <div class="contacts-address">
                            <div class="address-city font-title">{{ $contactsConfig->city_one }}</div>
                            <div class="address-itself">{{ $contactsConfig->address_one }}</div>
                        </div>

                        <div class="contacts-additional">
                            <div class="empty-gap"></div>
                            <div class="additional-phone">{{ $contactsConfig->phone_one }}</div>
                            <div class="additional-email">{{ $contactsConfig->email_one }}</div>
                        </div>

                    </div>
                    <div class="art-contacts-right">
                        {!! $contactsConfig->iframe_address_one !!}
                    </div>
                </div>

                <div class="art-contacts-line">
                    <div class="art-contacts-left">

                        <div class="contacts-address">
                            <div class="address-city font-title">{{ $contactsConfig->city_two }}</div>
                            <div class="address-itself">{{ $contactsConfig->address_two }}</div>
                        </div>

                        <div class="contacts-additional">
                            <div class="empty-gap"></div>
                            <div class="additional-phone">{{ $contactsConfig->phone_two }}</div>
                            <div class="additional-email">{{ $contactsConfig->email_two }}</div>
                        </div>

                    </div>
                    <div class="art-contacts-right">
                        {!! $contactsConfig->iframe_address_two !!}
                    </div>
                </div>

                <div class="art-contacts-line">
                    <div class="art-contacts-left">

                        <div class="contacts-address">
                            <div class="address-city font-title">{{ $contactsConfig->city_three }}</div>
                            <div class="address-itself">{{ $contactsConfig->address_three }}</div>
                        </div>

                        <div class="contacts-additional">
                            <div class="empty-gap"></div>
                            <div class="additional-phone">{{ $contactsConfig->phone_three }}</div>
                            <div class="additional-email">{{ $contactsConfig->email_three }}</div>
                        </div>

                    </div>
                    <div class="art-contacts-right">
                        {!! $contactsConfig->iframe_address_three !!}
                    </div>
                </div>

            @endif

        </div>
    </section>


@stop
