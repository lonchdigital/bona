<script>

import axios from "axios";
import MultiLanguageInputComponent from "../components/MultiLanguageInputComponent.vue";
import ImageFileInputComponent from "../components/ImageFileInputComponent.vue";
import ServicesSectionsComponent from "../components/ServicesSectionsComponent.vue";
import MultiLanguageRichTextEditorComponent from "../components/MultiLanguageRichTextEditorComponent.vue";
import * as transliteration from 'transliteration';


export default {
    components: {
        MultiLanguageInputComponent,
        ImageFileInputComponent,
        ServicesSectionsComponent,
        MultiLanguageRichTextEditorComponent,
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

        title: {
            type: Array,
            default: [],
        },
        description: {
            type: Array,
            default: [],
        },
        buttonText: {
            type: Array,
            default: [],
        },
        buttonUrl: {
            type: String,
            default: '',
        },
        imageUrl: {
            type: String,
            default: '',
        },
        videoIframe: {
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


                <multi-language-input-component
                    :title="$t('admin.title')"
                    name="title"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data=" (title) ? title : []"
                    :errors="errors"
                />

                <multi-language-rich-text-editor-component
                    :title="$t('admin.description')"
                    name="description"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :content="(description) ? description : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.text_button')"
                    name="button_text"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(buttonText) ? buttonText : []"
                    :errors="errors"
                />

                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.button_link')"
                        name="button_url"
                        :model-value="buttonUrl"
                        :errors="errors"
                        :is-required="false"
                    />
                </div>

                <image-file-input-component
                    :title="$t('admin.image')"
                    name="image"
                    image-deleted-name="image_deleted"
                    :is-required="false"
                    :errors="errors"
                    :init-data="(imageUrl) ? imageUrl : null"
                />

                <div class="form-group mb-3">
                    <input-component
                        title="iframe"
                        name="iframe"
                        :model-value="videoIframe"
                        :errors="errors"
                        :is-required="false"
                    />
                </div>

            </div>
        </div>
    </reactive-form-container>
</template>
