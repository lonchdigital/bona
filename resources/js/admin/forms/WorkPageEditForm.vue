<script>

import axios from "axios";
import MultiLanguageInputComponent from "../components/MultiLanguageInputComponent.vue";
import MultiLanguageRichTextEditorComponent from "../components/MultiLanguageRichTextEditorComponent.vue";
import ImageFileInputComponent from "../components/ImageFileInputComponent.vue";
import * as transliteration from 'transliteration';

export default {
    components: {
        MultiLanguageRichTextEditorComponent,
        MultiLanguageInputComponent,
        ImageFileInputComponent,
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

        workName: {
            type: Object,
            default: {},
        },
        workSlug: {
            type: String,
            default: '',
        },
        workMetaTitle: {
            type: Object,
            default: {},
        },
        workMetaDescription: {
            type: Object,
            default: {},
        },
        workMetaKeywords: {
            type: Object,
            default: {},
        },

        workImage: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            selectedLanguage: '',
            errors: [],
            workSlugData: '',
        }
    },
    created() {

    },
    mounted() {
        this.selectedLanguage = this.baseLanguage;
        this.workSlugData = this.workSlug;

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
        updateSlug(value) {
            if(this.selectedLanguage === 'uk'){
                value = event.target.value;
                this.workSlugData = transliteration.slugify(value);
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
        :card-body-title="$t('admin.product_information') "
    >
        <div class="row">
            <div class="col">

                <multi-language-input-component
                    :title="$t('admin.name')"
                    name="name"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="true"
                    :init-data="workName"
                    :errors="errors"
                    @input="updateSlug"
                />

                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.slug')"
                        :name="'slug'"
                        :model-value="workSlugData"
                        :errors="errors"
                        :is-required="true"
                    />
                </div>

<!--                <multi-language-input-component
                    :title="$t('admin.meta_title')"
                    name="meta_title"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="workMetaTitle"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.meta_description')"
                    name="meta_description"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="workMetaDescription"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.meta_keywords')"
                    name="meta_keywords"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="workMetaKeywords"
                    :errors="errors"
                />-->

                <div class="form-group mb-3">
                    <image-file-input-component
                        :title="$t('admin.work_image') + ' ' + $t('admin.work_image_requirements')"
                        name="main_image"
                        image-deleted-name="'main_image[image_deleted]'"
                        :is-required="true"
                        :errors="errors"
                        :init-data="workImage"
                    />
                </div>

            </div>
        </div>
    </reactive-form-container>
</template>
