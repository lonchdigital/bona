<script>

import axios from "axios";
import MultiLanguageInputComponent from "../components/MultiLanguageInputComponent.vue";
import ImageFileInputComponent from "../components/ImageFileInputComponent.vue";
import ServicesSectionsComponent from "../components/ServicesSectionsComponent.vue";
// import MultiLanguageRichTextEditorComponent from "../components/MultiLanguageRichTextEditorComponent.vue";
import MultiLanguageTextAreaComponent from "../components/MultiLanguageTextAreaComponent.vue";
import * as transliteration from 'transliteration';


export default {
    components: {
        MultiLanguageInputComponent,
        ImageFileInputComponent,
        ServicesSectionsComponent,
        MultiLanguageTextAreaComponent
        // MultiLanguageRichTextEditorComponent,
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


        logoLightUrl: {
            type: String,
            default: '',
        },
        logoDarkUrl: {
            type: String,
            default: '',
        },

        instagram: {
            type: String,
            default: '',
        },
        telegram: {
            type: String,
            default: '',
        },
        viber: {
            type: String,
            default: '',
        },
        facebook: {
            type: String,
            default: '',
        },
        phoneOne: {
            type: String,
            default: '',
        },
        footerText: {
            type: Object,
            default: [],
        },

        formTitle: {
            type: Object,
            default: [],
        },
        formText: {
            type: Object,
            default: [],
        },
        formImageUrl: {
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
        :card-body-title="$t('admin.application_config') "
    >
        <div class="row">
            <div class="col">

                <image-file-input-component
                    :title="$t('admin.logo_light')"
                    name="logo_light"
                    image-deleted-name="logo_light_deleted"
                    :is-required="false"
                    :errors="errors"
                    :init-data="(logoLightUrl) ? logoLightUrl : null"
                />

                <image-file-input-component
                    :title="$t('admin.logo_dark')"
                    name="logo_dark"
                    image-deleted-name="logo_dark_deleted"
                    :is-required="false"
                    :errors="errors"
                    :init-data="(logoDarkUrl) ? logoDarkUrl : null"
                />

                <div class="form-group mb-3">
                    <input-component
                        title="Instagram"
                        name="instagram"
                        :model-value="instagram"
                        :errors="errors"
                        :is-required="false"
                    />
                </div>

                <div class="form-group mb-3">
                    <input-component
                        title="Telegram"
                        name="telegram"
                        :model-value="telegram"
                        :errors="errors"
                        :is-required="false"
                    />
                </div>

                <div class="form-group mb-3">
                    <input-component
                        title="Viber"
                        name="viber"
                        :model-value="viber"
                        :errors="errors"
                        :is-required="false"
                    />
                </div>

                <div class="form-group mb-3">
                    <input-component
                        title="Facebook"
                        name="facebook"
                        :model-value="facebook"
                        :errors="errors"
                        :is-required="false"
                    />
                </div>

                <div class="form-group mb-3">
                    <input-component
                        title="Phone #1"
                        name="phone_one"
                        :model-value="phoneOne"
                        :errors="errors"
                        :is-required="false"
                    />
                </div>

                <p class="mt-5">
                    <strong>
                        {{ $t('admin.footer_settings') }}
                    </strong>
                </p>

                <multi-language-text-area-component
                    title="Footer text"
                    name="footer_text"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="footerText"
                    :errors="errors"
                />


                <p class="mt-5">
                    <strong>
                        {{ $t('admin.contact_form') }}
                    </strong>
                </p>

                <multi-language-input-component
                    title="Form title"
                    name="form_title"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="formTitle"
                    :errors="errors"
                />

                <multi-language-text-area-component
                    title="Form text"
                    name="form_text"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="formText"
                    :errors="errors"
                />

                <image-file-input-component
                    title="Form Image"
                    name="form_image"
                    image-deleted-name="form_image_deleted"
                    :is-required="false"
                    :errors="errors"
                    :init-data="(formImageUrl) ? formImageUrl : null"
                />


            </div>
        </div>
    </reactive-form-container>
</template>
