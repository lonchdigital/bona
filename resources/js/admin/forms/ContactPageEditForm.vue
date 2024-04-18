<script>

import axios from "axios";
import MultiLanguageInputComponent from "../components/MultiLanguageInputComponent.vue";
import TextAreaComponent from "../components/TextAreaComponent.vue";
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

        pageMetaTitle: {
            type: Object,
            default: {},
        },
        pageMetaDescription: {
            type: Object,
            default: {},
        },
        pageMetaKeywords: {
            type: Object,
            default: {},
        },
        pageMetaTags: {
            type: String,
            default: '',
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
        :card-body-title="$t('admin.edit_page') "
    >
        <div class="row">
            <div class="col">

                <multi-language-input-component
                    :title="$t('admin.meta_title')"
                    name="meta_title"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="pageMetaTitle"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.meta_description')"
                    name="meta_description"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="pageMetaDescription"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.meta_keywords')"
                    name="meta_keywords"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="pageMetaKeywords"
                    :errors="errors"
                />


                <p>
                    <strong>
                        {{ $t('admin.contact_address_one') }}
                    </strong>
                </p>

                <multi-language-input-component
                    :title="$t('admin.city')"
                    name="city_one"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(cityOne) ? cityOne : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.address')"
                    name="address_one"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(addressOne) ? addressOne : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.phone')"
                    name="phone_one"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(phoneOne) ? phoneOne : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.email')"
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
                    :title="$t('admin.city')"
                    name="city_two"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(cityTwo) ? cityTwo : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.address')"
                    name="address_two"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(addressTwo) ? addressTwo : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.phone')"
                    name="phone_two"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(phoneTwo) ? phoneTwo : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.email')"
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
                    :title="$t('admin.city')"
                    name="city_three"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(cityThree) ? cityThree : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.address')"
                    name="address_three"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(addressThree) ? addressThree : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.phone')"
                    name="phone_three"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(phoneThree) ? phoneThree : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.email')"
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

                <text-area-component
                    :title="$t('admin.meta_tags')"
                    name="meta_tags"
                    :is-required="false"
                    :init-data="pageMetaTags"
                    :errors="errors"
                />

            </div>
        </div>
    </reactive-form-container>
</template>
