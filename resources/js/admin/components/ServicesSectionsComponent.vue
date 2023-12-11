<script>
import axios from "axios";
import MultiLanguageInputComponent from "./MultiLanguageInputComponent.vue";
// import MultiLanguageRichTextEditorComponent from "./MultiLanguageRichTextEditorComponent";

export default {
    components: {
        // MultiLanguageRichTextEditorComponent,
        MultiLanguageInputComponent
    },
    props: {
        sectionId: {
            type: Number,
            default: null,
        },
        section: {
            type: Object,
            default: {},
        },
        index: {
            type: Number,
            default: 0,
        },
        baseLanguage: {
            type: String,
            default: 'uk',
        },
        selectedLanguage: {
            type: String,
            default: 'uk',
        },
        availableLanguages: {
            type: Array,
            default: ['uk', 'ru'],
        },
        errors: {
            type: Object,
            default: [],
        }
    },
    emits: [
        'deleteSection',
    ],
    data () {
        return {
            slideDescription: [],
        }
    },
}
</script>

<template>
    <div class="row art-repeater-row" :key="sectionId">
        <div class="col">
            <div class="row">
                <div class="col">
                    <input type="hidden" v-if="sectionId !== null" :name="'sections[' + index + '][id]'" :value="sectionId">


                    <multi-language-input-component
                        :title="$t('admin.section_title')"
                        :name="'sections[' + index + '][title]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="false"
                        :init-data="section.hasOwnProperty('title') ? section.title : []"
                        :errors="errors"
                    />

                    <multi-language-rich-text-editor-component
                        :title="$t('admin.section_description')"
                        :name="'sections[' + index + '][description]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="false"
                        :content="(section.hasOwnProperty('description') && section.description !== null) ? section.description : []"
                        :errors="errors"
                    />

                    <multi-language-input-component
                        :title="$t('admin.section_text_button')"
                        :name="'sections[' + index + '][button_text]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="true"
                        :init-data="section.hasOwnProperty('button_text') ? section.button_text : []"
                        :errors="errors"
                    />

                    <div class="form-group mb-3">
                        <input-component
                            :title="$t('admin.section_text_link')"
                            :name="'sections[' + index + '][button_url]'"
                            :model-value="section.button_url"
                            :errors="errors"
                            :is-required="false"
                        />
                    </div>

                    <image-file-input-component
                        :title="$t('admin.section_image')"
                        :name="'sections[' + index + '][image]'"
                        :image-deleted-name="'sections[' + index + '][image_deleted]'"
                        :is-required="true"
                        :errors="errors"
                        :init-data="section.hasOwnProperty('section_image_url') ? section.section_image_url : null"
                    />

                </div>
            </div>
            <div class="row">
                <div class="col">
                    <a href="#" id="add-option" class="btn mb-2 btn-danger" @click.prevent="() => $emit('deleteSection', index)"><span class="fe fe-trash fe-16 mr-2"></span>{{ $t('admin.section_delete')}}</a>
                </div>
            </div>
        </div>
    </div>
</template>
