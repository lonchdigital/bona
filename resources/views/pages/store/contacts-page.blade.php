@extends('layouts.store-main')

@section('title')

    @if(isset($contactsConfig))
        @if($contactsConfig->meta_title)
            <title>{{ $contactsConfig->meta_title }}</title>
            <meta name="title" content="{{ $contactsConfig->meta_title }}">
        @endif

        @if($contactsConfig->meta_description)
            <meta name="description" content="{{ $contactsConfig->meta_description }}">
        @endif
        @if($contactsConfig->meta_keywords)
            <meta name="keywords" content="{{ $contactsConfig->meta_keywords }}">
        @endif

        @if($contactsConfig->meta_tags)
            {!! $contactsConfig->meta_tags !!}
        @endif
    @endif

@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['#' => 'contacts']])

    <section class="art-contacts-page-section common-page-section-wrapper art-section-pd">
        <div class="container">

            <div class="row">
                <header class=" col-12 art-header-left">
                    <div>
                        <h1 class="title">{{ trans('base.contacts') }}</h1>
                    </div>
                </header>
            </div>

            @if( !is_null($contactsConfig) )

                <div class="art-contacts-line">
                    <div class="art-contacts-left">

                        <div class="contacts-address">
                            <div class="address-city font-title">{{ $contactsConfig->city_one }}</div>
                            <div class="address-itself">{{ $contactsConfig->address_one }}</div>
                        </div>

                        <div class="contacts-additional">
                            <div class="empty-gap"></div>
                            <div class="additional-phone"><a href="tel:{{ str_replace(' ', '', $contactsConfig->phone_one) }}">{{ $contactsConfig->phone_one }}</a></div>
                            <div class="additional-email"><a href="mailto:{{ $contactsConfig->email_one }}">{{ $contactsConfig->email_one }}</a></div>
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
                            <div class="additional-phone"><a href="tel:{{ str_replace(' ', '', $contactsConfig->phone_two) }}">{{ $contactsConfig->phone_two }}</a></div>
                            <div class="additional-email"><a href="mailto:{{ $contactsConfig->email_two }}">{{ $contactsConfig->email_two }}</a></div>
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
                            <div class="additional-phone"><a href="tel:{{ str_replace(' ', '', $contactsConfig->phone_three) }}">{{ $contactsConfig->phone_three }}</a></div>
                            <div class="additional-email"><a href="mailto:{{ $contactsConfig->email_three }}">{{ $contactsConfig->email_three }}</a></div>
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
