<script>

import axios from "axios";
import MultiLanguageInputComponent from "../components/MultiLanguageInputComponent.vue";
import MultiLanguageRichTextEditorComponent from "../components/MultiLanguageRichTextEditorComponent.vue";
import ImageFileInputComponent from "../components/ImageFileInputComponent.vue";
import MultiLanguageTextAreaComponent from "../components/MultiLanguageTextAreaComponent.vue";
import WorkImageComponent from "../components/WorkImageComponent.vue";
import * as transliteration from 'transliteration';

export default {
    components: {
        MultiLanguageRichTextEditorComponent,
        MultiLanguageInputComponent,
        MultiLanguageTextAreaComponent,
        ImageFileInputComponent,
        WorkImageComponent,
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

        workIntro: {
            type: Object,
            default: {},
        },
        workDescription: {
            type: Object,
            default: {},
        },
        workClientQuote: {
            type: Object,
            default: {},
        },
        workLocation: {
            type: String,
            default: '',
        },
        workDoorsCount: {
            type: [String, Number],
            default: '',
        },
        workDuration: {
            type: String,
            default: '',
        },
        workClientName: {
            type: String,
            default: '',
        },
        workIsPublished: {
            type: Boolean,
            default: true,
        },
        workImages: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            selectedLanguage: '',
            errors: [],
            workSlugData: '',
            images: [],
        }
    },
    created() {

    },
    mounted() {
        this.selectedLanguage = this.baseLanguage;
        this.workSlugData = this.workSlug;
        this.images = this.workImages ? [...this.workImages] : [];
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
        addImage() {
            this.images.push({});
        },
        deleteImage(index) {
            this.images.splice(index, 1);
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

                <multi-language-text-area-component
                    :title="$t('admin.work_intro')"
                    name="intro"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="workIntro"
                    :errors="errors"
                />

                <multi-language-rich-text-editor-component
                    :title="$t('admin.work_description')"
                    name="description"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="workDescription"
                    :errors="errors"
                />

                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.work_location')"
                        name="location"
                        :model-value="workLocation"
                        :errors="errors"
                    />
                </div>

                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.work_doors_count')"
                        name="doors_count"
                        type="number"
                        :model-value="workDoorsCount"
                        :errors="errors"
                    />
                </div>

                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.work_duration')"
                        name="duration"
                        :model-value="workDuration"
                        :errors="errors"
                    />
                </div>

                <multi-language-text-area-component
                    :title="$t('admin.work_client_quote')"
                    name="client_quote"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="workClientQuote"
                    :errors="errors"
                />

                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.work_client_name')"
                        name="client_name"
                        :model-value="workClientName"
                        :errors="errors"
                    />
                </div>

                <div class="form-group mb-3">
                    <check-box-component
                        :title="$t('admin.work_is_published')"
                        name="is_published"
                        :model-value="workIsPublished"
                        :errors="errors"
                    />
                </div>

                <hr>

                <p><strong>{{ $t('admin.work_gallery') }}</strong></p>

                <div class="form-group mb-3 art-admin-repeater-four-width">
                    <work-image-component
                        v-for="(image, index) in images"
                        :key="index"
                        :image-id="image.hasOwnProperty('id') ? image.id : null"
                        :image="image"
                        :index="index"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :errors="errors"
                        @delete-image="() => deleteImage(index)"
                    />
                </div>

                <div class="row">
                    <div class="col">
                        <a href="#" class="btn mb-2 btn-secondary" @click.prevent="addImage">
                            <span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.work_gallery_add') }}
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </reactive-form-container>
</template>
