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


                <p>
                    <strong>
                        {{ $t('admin.services_sections') }}
                    </strong>
                </p>

                <services-sections-component
                    v-for="(section, index) in sections"
                    :section-id="section.hasOwnProperty('id') ? section.id : null"
                    :section="section"
                    :index="index"
                    :base-language="baseLanguage"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :errors="errors"
                    @delete-section="() => deleteSection(index)"
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
