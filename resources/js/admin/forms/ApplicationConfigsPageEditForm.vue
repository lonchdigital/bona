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
        contactsRoute: {
            type: String,
            default: '',
        },
        menuSettingsRoute: {
            type: String,
            default: '',
        },
        initialTab: {
            type: String,
            default: 'main',
            validator: (value) => ['main', 'footer'].includes(value),
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
        tiktok: {
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

        authorName: {
            type: Object,
            default: [],
        },
        authorDescription: {
            type: Object,
            default: [],
        },
        authorAvatarUrl: {
            type: String,
            default: '',
        },

    },
    data() {
        return {
            selectedLanguage: '',
            selectedFieldId: null,
            errors: [],
            activeSettingsTab: this.initialTab,
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

            const footerFields = [
                'logo_light',
                'footer_text',
                'instagram',
                'telegram',
                'viber',
                'facebook',
                'tiktok',
            ];

            if (Object.keys(errors || {}).some((key) => footerFields.some((field) => key.startsWith(field)))) {
                this.activeSettingsTab = 'footer';
            }
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
        <ul class="nav nav-tabs mb-4" role="tablist" :aria-label="$t('admin.application_config')">
            <li class="nav-item" role="presentation">
                <button
                    id="application-settings-main-tab"
                    class="nav-link"
                    :class="{ active: activeSettingsTab === 'main' }"
                    type="button"
                    role="tab"
                    aria-controls="application-settings-main"
                    :aria-selected="activeSettingsTab === 'main' ? 'true' : 'false'"
                    @click="activeSettingsTab = 'main'"
                >
                    {{ $t('admin.site_settings_main') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    id="application-settings-footer-tab"
                    class="nav-link"
                    :class="{ active: activeSettingsTab === 'footer' }"
                    type="button"
                    role="tab"
                    aria-controls="application-settings-footer"
                    :aria-selected="activeSettingsTab === 'footer' ? 'true' : 'false'"
                    @click="activeSettingsTab = 'footer'"
                >
                    {{ $t('admin.footer_settings') }}
                </button>
            </li>
        </ul>

        <div class="row">
            <div class="col">
                <section
                    id="application-settings-main"
                    role="tabpanel"
                    aria-labelledby="application-settings-main-tab"
                    v-show="activeSettingsTab === 'main'"
                >
                <image-file-input-component
                    :title="$t('admin.logo_dark')"
                    name="logo_dark"
                    image-deleted-name="logo_dark_deleted"
                    :is-required="false"
                    :errors="errors"
                    :init-data="(logoDarkUrl) ? logoDarkUrl : null"
                />

                <input type="hidden" name="phone_one" :value="phoneOne">

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



                <p class="mt-5">
                    <strong>
                        {{ $t('admin.author') }}
                    </strong>
                </p>

                <multi-language-input-component
                    title="Author name"
                    name="author_name"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="authorName"
                    :errors="errors"
                />

                <multi-language-input-component
                    title="Author description"
                    name="author_description"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="authorDescription"
                    :errors="errors"
                />

                <image-file-input-component
                    title="Author avatar"
                    name="author_avatar"
                    image-deleted-name="author_avatar_deleted"
                    :is-required="false"
                    :errors="errors"
                    :init-data="(authorAvatarUrl) ? authorAvatarUrl : null"
                />
                </section>

                <section
                    id="application-settings-footer"
                    role="tabpanel"
                    aria-labelledby="application-settings-footer-tab"
                    v-show="activeSettingsTab === 'footer'"
                >
                    <div class="alert alert-info mb-4">
                        <p class="mb-2">{{ $t('admin.footer_settings_help') }}</p>
                        <div class="d-flex flex-wrap" style="gap: 8px">
                            <a v-if="contactsRoute" class="btn btn-sm btn-outline-dark" :href="contactsRoute">
                                {{ $t('admin.footer_edit_contacts') }}
                            </a>
                            <a v-if="menuSettingsRoute" class="btn btn-sm btn-outline-dark" :href="menuSettingsRoute">
                                {{ $t('admin.footer_edit_menus') }}
                            </a>
                        </div>
                    </div>

                    <image-file-input-component
                        :title="$t('admin.logo_light_footer')"
                        name="logo_light"
                        image-deleted-name="logo_light_deleted"
                        :is-required="false"
                        :errors="errors"
                        :init-data="(logoLightUrl) ? logoLightUrl : null"
                    />

                    <multi-language-text-area-component
                        :title="$t('admin.footer_description')"
                        name="footer_text"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="false"
                        :init-data="footerText"
                        :errors="errors"
                    />

                    <p class="mt-5 mb-3"><strong>{{ $t('admin.footer_socials') }}</strong></p>

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
                            title="TikTok"
                            name="tiktok"
                            :model-value="tiktok"
                            :errors="errors"
                            :is-required="false"
                        />
                    </div>
                </section>


            </div>
        </div>
    </reactive-form-container>
</template>
