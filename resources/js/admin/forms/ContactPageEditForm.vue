<script>

import axios from "axios";
import MultiLanguageInputComponent from "../components/MultiLanguageInputComponent.vue";
import * as transliteration from 'transliteration';


export default {
    components: {
        MultiLanguageInputComponent,
    },
    props: {
        submitRoute: {
            type: String,
            default: '',
        },
        backRoute: {
            type: String,
            default: '',
        },
        availableLanguages: {
            type: Array,
            default: ['uk', 'ru'],
        },
        baseLanguage: {
            type: String,
            default: 'uk',
        },


        cityOne: {
            type: Array,
            default: [],
        },
        addressOne: {
            type: Array,
            default: [],
        },
        phoneOne: {
            type: Array,
            default: [],
        },
        emailOne: {
            type: Array,
            default: [],
        },
        iframeOne: {
            type: String,
            default: '',
        },

        cityTwo: {
            type: Array,
            default: [],
        },
        addressTwo: {
            type: Array,
            default: [],
        },
        phoneTwo: {
            type: Array,
            default: [],
        },
        emailTwo: {
            type: Array,
            default: [],
        },
        iframeTwo: {
            type: String,
            default: '',
        },

        cityThree: {
            type: Array,
            default: [],
        },
        addressThree: {
            type: Array,
            default: [],
        },
        phoneThree: {
            type: Array,
            default: [],
        },
        emailThree: {
            type: Array,
            default: [],
        },
        iframeThree: {
            type: String,
            default: '',
        },

    },
    data() {
        return {
            selectedLanguage: '',
            selectedFieldId: null,
            errors: [],
        }
    },
    created() {

    },
    mounted() {
        this.selectedLanguage = this.baseLanguage;

    },
    computed: {
        /*slug() {
            return transliteration.slugify(this.produktName);
        }*/
    },
    watch: {
        selectedFieldId() {
            this.selectedOptions = [];
        }
    },
    methods: {

        changeSelectedLanguage(newSelectedLanguage) {
            this.selectedLanguage = newSelectedLanguage;
        },
        handleFormSubmit(errors) {
            this.errors = errors;
        },

    }

}
</script>

<template>
    <reactive-form-container
        :submit-route="submitRoute"
        :back-route="backRoute"
        @on-selected-language-change="changeSelectedLanguage"
        @on-errors-change="handleFormSubmit"
        :card-body-title="$t('admin.delivery_edit_page') "
    >
        <div class="row">
            <div class="col">

                <p>
                    <strong>
                        {{ $t('admin.contact_address_one') }}
                    </strong>
                </p>

                <multi-language-input-component
                    :title="$t('admin.contact_city')"
                    name="city_one"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(cityOne) ? cityOne : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.contact_address')"
                    name="address_one"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(addressOne) ? addressOne : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.contact_phone')"
                    name="phone_one"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(phoneOne) ? phoneOne : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.contact_email')"
                    name="email_one"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(emailOne) ? emailOne : []"
                    :errors="errors"
                />

                <div class="form-group mb-3">
                    <input-component
                        title="iframe #1"
                        name="iframe_address_one"
                        :model-value="iframeOne"
                        :errors="errors"
                        :is-required="false"
                    />
                </div>




                <br>
                <p class="mt-4">
                    <strong>
                        {{ $t('admin.contact_address_two') }}
                    </strong>
                </p>

                <multi-language-input-component
                    :title="$t('admin.contact_city')"
                    name="city_two"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(cityTwo) ? cityTwo : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.contact_address')"
                    name="address_two"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(addressTwo) ? addressTwo : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.contact_phone')"
                    name="phone_two"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(phoneTwo) ? phoneTwo : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.contact_email')"
                    name="email_two"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(emailTwo) ? emailTwo : []"
                    :errors="errors"
                />

                <div class="form-group mb-3">
                    <input-component
                        title="iframe #2"
                        name="iframe_address_two"
                        :model-value="iframeTwo"
                        :errors="errors"
                        :is-required="false"
                    />
                </div>


                <br>
                <p class="mt-4">
                    <strong>
                        {{ $t('admin.contact_address_three') }}
                    </strong>
                </p>

                <multi-language-input-component
                    :title="$t('admin.contact_city')"
                    name="city_three"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(cityThree) ? cityThree : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.contact_address')"
                    name="address_three"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(addressThree) ? addressThree : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.contact_phone')"
                    name="phone_three"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(phoneThree) ? phoneThree : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.contact_email')"
                    name="email_three"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(emailThree) ? emailThree : []"
                    :errors="errors"
                />

                <div class="form-group mb-3">
                    <input-component
                        title="iframe #3"
                        name="iframe_address_three"
                        :model-value="iframeThree"
                        :errors="errors"
                        :is-required="false"
                    />
                </div>


            </div>
        </div>
    </reactive-form-container>
</template>
