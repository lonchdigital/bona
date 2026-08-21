<script>
import MultiLanguageInputComponent from "../components/MultiLanguageInputComponent.vue";
import MultiLanguageTextAreaComponent from "../components/MultiLanguageTextAreaComponent.vue";
import MultiLanguageRichTextEditorComponent from "../components/MultiLanguageRichTextEditorComponent.vue";
import ImageFileInputComponent from "../components/ImageFileInputComponent.vue";
import InputComponent from "../components/InputComponent.vue";
import AuthorCertificateComponent from "../components/AuthorCertificateComponent.vue";
import * as transliteration from 'transliteration';

export default {
    components: {
        MultiLanguageInputComponent,
        MultiLanguageTextAreaComponent,
        MultiLanguageRichTextEditorComponent,
        ImageFileInputComponent,
        InputComponent,
        AuthorCertificateComponent,
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

        authorName: {
            type: Object,
            default: {},
        },
        authorSlug: {
            type: String,
            default: '',
        },
        authorJobTitle: {
            type: Object,
            default: {},
        },
        authorShortDescription: {
            type: Object,
            default: {},
        },
        authorBiography: {
            type: Object,
            default: {},
        },
        authorPhoto: {
            type: String,
            default: '',
        },
        authorInstagramUrl: {
            type: String,
            default: '',
        },
        authorFacebookUrl: {
            type: String,
            default: '',
        },
        authorLinkedinUrl: {
            type: String,
            default: '',
        },
        authorMetaTitle: {
            type: Object,
            default: {},
        },
        authorMetaDescription: {
            type: Object,
            default: {},
        },
        authorMetaKeywords: {
            type: Object,
            default: {},
        },
        authorCertificates: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            selectedLanguage: '',
            errors: [],
            authorSlugData: '',
            certificates: [],
        }
    },
    mounted() {
        this.selectedLanguage = this.baseLanguage;
        this.authorSlugData = this.authorSlug;
        this.certificates = this.authorCertificates ? [...this.authorCertificates] : [];
    },
    methods: {
        changeSelectedLanguage(newSelectedLanguage) {
            this.selectedLanguage = newSelectedLanguage;
        },
        handleFormSubmit(errors) {
            this.errors = errors;
        },
        updateSlug(value) {
            if (this.selectedLanguage === 'uk') {
                value = event.target.value;
                this.authorSlugData = transliteration.slugify(value);
            }
        },
        addCertificate() {
            this.certificates.push({});
        },
        deleteCertificate(index) {
            this.certificates.splice(index, 1);
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
        :card-body-title="$t('admin.authors')"
    >
        <div class="row">
            <div class="col">

                <multi-language-input-component
                    :title="$t('admin.author_name')"
                    name="name"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="true"
                    :init-data="authorName"
                    :errors="errors"
                    @input="updateSlug"
                />

                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.slug')"
                        name="slug"
                        :model-value="authorSlugData"
                        :errors="errors"
                        :is-required="true"
                    />
                </div>

                <multi-language-input-component
                    :title="$t('admin.author_job_title')"
                    name="job_title"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="authorJobTitle"
                    :errors="errors"
                />

                <multi-language-text-area-component
                    :title="$t('admin.author_short_description')"
                    name="short_description"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="authorShortDescription"
                    :errors="errors"
                />

                <multi-language-rich-text-editor-component
                    :title="$t('admin.author_biography')"
                    name="biography"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="authorBiography"
                    :errors="errors"
                />

                <div class="form-group mb-3">
                    <image-file-input-component
                        :title="$t('admin.author_photo')"
                        name="photo"
                        image-deleted-name="'photo[image_deleted]'"
                        :is-required="false"
                        :errors="errors"
                        :init-data="authorPhoto"
                    />
                </div>

                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.author_instagram_url')"
                        name="instagram_url"
                        :model-value="authorInstagramUrl"
                        :errors="errors"
                    />
                </div>

                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.author_facebook_url')"
                        name="facebook_url"
                        :model-value="authorFacebookUrl"
                        :errors="errors"
                    />
                </div>

                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.author_linkedin_url')"
                        name="linkedin_url"
                        :model-value="authorLinkedinUrl"
                        :errors="errors"
                    />
                </div>

                <multi-language-input-component
                    :title="$t('admin.meta_title')"
                    name="meta_title"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="authorMetaTitle"
                    :errors="errors"
                />

                <multi-language-text-area-component
                    :title="$t('admin.meta_description')"
                    name="meta_description"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="authorMetaDescription"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.meta_keywords')"
                    name="meta_keywords"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="authorMetaKeywords"
                    :errors="errors"
                />

                <hr>

                <p><strong>{{ $t('admin.author_certificates') }}</strong></p>

                <div class="form-group mb-3 art-admin-repeater-four-width">
                    <author-certificate-component
                        v-for="(certificate, index) in certificates"
                        :key="index"
                        :certificate-id="certificate.hasOwnProperty('id') ? certificate.id : null"
                        :certificate="certificate"
                        :index="index"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :errors="errors"
                        @delete-certificate="() => deleteCertificate(index)"
                    />
                </div>

                <div class="row">
                    <div class="col">
                        <a href="#" class="btn mb-2 btn-secondary" @click.prevent="addCertificate">
                            <span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.author_certificate_add') }}
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </reactive-form-container>
</template>
