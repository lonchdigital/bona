<script>

import axios from "axios";
import MultiLanguageInputComponent from "../components/MultiLanguageInputComponent.vue";
import ImageFileInputComponent from "../components/ImageFileInputComponent.vue";
import ServicesSectionsComponent from "../components/ServicesSectionsComponent.vue";
import MultiLanguageRichTextEditorComponent from "../components/MultiLanguageRichTextEditorComponent.vue";
import TextAreaComponent from "../components/TextAreaComponent.vue";
import HomePageFaqComponent from "../components/HomePageFaqComponent.vue";
import * as transliteration from 'transliteration';


export default {
    components: {
        MultiLanguageInputComponent,
        ImageFileInputComponent,
        ServicesSectionsComponent,
        MultiLanguageRichTextEditorComponent,
        TextAreaComponent,
        HomePageFaqComponent,
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
        pageTitle: {
            type: Object,
            default: () => ({}),
        },
        pageIntro: {
            type: Object,
            default: () => ({}),
        },
        pageContent: {
            type: Object,
            default: () => ({}),
        },
        pageFaqs: {
            type: Array,
            default: () => [],
        },
        pageMetaDescription: {
            type: Object,
            default: {},
        },
        pageMetaKeywords: {
            type: Object,
            default: {},
        },
        productMetaTags: {
            type: String,
            default: '',
        },

        serviceSections: {
            type: Array,
            default: [],
        },


    },
    data() {
        return {
            sections: [],
            selectedLanguage: '',
            selectedFieldId: null,
            errors: [],
            faqsData: this.pageFaqs.map((faq) => ({...faq})),
        }
    },
    created() {

    },
    mounted() {
        this.selectedLanguage = this.baseLanguage;

        if (this.serviceSections) {
            this.sections = this.serviceSections;
        }

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

        addSection() {
            this.sections.push({});
        },
        deleteSection(index) {
            this.sections.splice(index, 1);
        },
        moveSection(index, direction) {
            const nextIndex = index + direction;
            if (nextIndex < 0 || nextIndex >= this.sections.length) {
                return;
            }

            const section = this.sections.splice(index, 1)[0];
            this.sections.splice(nextIndex, 0, section);
        },
        addFaq() {
            this.faqsData.push({question: {}, answer: {}});
        },
        deleteFaq(index) {
            this.faqsData.splice(index, 1);
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
        :card-body-title="$t('admin.service_edit_page') "
    >
        <div class="row">
            <div class="col">

                <multi-language-input-component
                    :title="$t('admin.title')"
                    name="title"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="pageTitle"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.service_intro')"
                    name="intro"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="pageIntro"
                    :errors="errors"
                />

                <multi-language-rich-text-editor-component
                    :title="$t('admin.service_page_content')"
                    name="content"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :content="pageContent"
                    :errors="errors"
                />

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

                <text-area-component
                    :title="$t('admin.meta_tags')"
                    name="meta_tags"
                    :is-required="false"
                    :init-data="productMetaTags"
                    :errors="errors"
                />

                <input type="hidden" name="faqs_managed" value="1">
                <p class="mt-4"><strong>{{ $t('admin.questions') }}</strong></p>
                <div class="form-group mb-3 art-admin-repeater-four-width">
                    <home-page-faq-component
                        v-for="(faq, index) in faqsData"
                        :key="faq.id || `services-page-faq-${index}`"
                        :faq-id="faq.hasOwnProperty('id') ? faq.id : null"
                        :faq="faq"
                        :index="index"
                        :base-language="baseLanguage"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :errors="errors"
                        @delete-faq="deleteFaq(index)"
                    />
                </div>
                <button type="button" class="btn mb-4 btn-secondary" @click="addFaq">
                    <span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.question_add') }}
                </button>


                <p>
                    <strong>
                        {{ $t('admin.services_sections') }}
                    </strong>
                </p>

                <services-sections-component
                    v-for="(section, index) in sections"
                    :key="section.id || 'new-' + index"
                    :section-id="section.hasOwnProperty('id') ? section.id : null"
                    :section="section"
                    :index="index"
                    :is-last="index === sections.length - 1"
                    :base-language="baseLanguage"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :errors="errors"
                    @delete-section="() => deleteSection(index)"
                    @move-section="(direction) => moveSection(index, direction)"
                />

                <div class="row">
                    <div class="col">
                        <a href="#" id="add-option" class="btn mb-2 btn-secondary" @click.prevent="addSection"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.section_add')}}</a>
                    </div>
                </div>

            </div>
        </div>
    </reactive-form-container>
</template>
