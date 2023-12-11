@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">Contacts</h2>

                <contact-page-edit-form
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    submit-route="{{ route('admin.contacts.edit') }}"

                    @if( !is_null($contactsConfig) )
                        :city-one="{{ json_encode($contactsConfig->getTranslations('city_one')) }}"
                        :address-one="{{ json_encode($contactsConfig->getTranslations('address_one')) }}"
                        :phone-one="{{ json_encode($contactsConfig->getTranslations('phone_one')) }}"
                        :email-one="{{ json_encode($contactsConfig->getTranslations('email_one')) }}"
                        :iframe-one="{{ json_encode($contactsConfig->iframe_address_one) }}"

                        :city-two="{{ json_encode($contactsConfig->getTranslations('city_two')) }}"
                        :address-two="{{ json_encode($contactsConfig->getTranslations('address_two')) }}"
                        :phone-two="{{ json_encode($contactsConfig->getTranslations('phone_two')) }}"
                        :email-two="{{ json_encode($contactsConfig->getTranslations('email_two')) }}"
                        :iframe-two="{{ json_encode($contactsConfig->iframe_address_two) }}"

                        :city-three="{{ json_encode($contactsConfig->getTranslations('city_three')) }}"
                        :address-three="{{ json_encode($contactsConfig->getTranslations('address_three')) }}"
                        :phone-three="{{ json_encode($contactsConfig->getTranslations('phone_three')) }}"
                        :email-three="{{ json_encode($contactsConfig->getTranslations('email_three')) }}"
                        :iframe-three="{{ json_encode($contactsConfig->iframe_address_three) }}"
                    @endif
                />

            </div>
        </div>
    </div>
@endsection
@section('vue')
    <vue/>
@endsection
