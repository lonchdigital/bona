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
        isLast: {
            type: Boolean,
            default: false,
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
        'moveSection',
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
                        :title="$t('admin.title')"
                        :name="'sections[' + index + '][title]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="false"
                        :init-data="section.hasOwnProperty('title') ? section.title : []"
                        :errors="errors"
                    />

                    <multi-language-rich-text-editor-component
                        :title="$t('admin.description')"
                        :name="'sections[' + index + '][description]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="false"
                        :content="(section.hasOwnProperty('description') && section.description !== null) ? section.description : []"
                        :errors="errors"
                    />

                    <multi-language-input-component
                        :title="$t('admin.service_intro')"
                        :name="'sections[' + index + '][intro]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="false"
                        :init-data="section.hasOwnProperty('intro') && section.intro !== null ? section.intro : []"
                        :errors="errors"
                    />

                    <multi-language-rich-text-editor-component
                        :title="$t('admin.service_page_content')"
                        :name="'sections[' + index + '][content]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="false"
                        :content="section.hasOwnProperty('content') && section.content !== null ? section.content : []"
                        :errors="errors"
                    />

                    <div class="form-group mb-3">
                        <input-component
                            :title="$t('admin.slug')"
                            :name="'sections[' + index + '][slug]'"
                            :model-value="section.slug || ''"
                            :errors="errors"
                            :is-required="true"
                        />
                        <small class="form-text text-muted">{{ $t('admin.service_slug_help') }}</small>
                    </div>

                    <multi-language-input-component
                        :title="$t('admin.text_button')"
                        :name="'sections[' + index + '][button_text]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="true"
                        :init-data="section.hasOwnProperty('button_text') ? section.button_text : []"
                        :errors="errors"
                    />

                    <div class="form-group mb-3">
                        <input-component
                            :title="$t('admin.button_link')"
                            :name="'sections[' + index + '][button_url]'"
                            :model-value="section.button_url"
                            :errors="errors"
                            :is-required="false"
                        />
                    </div>

                    <image-file-input-component
                        :title="$t('admin.image')"
                        :name="'sections[' + index + '][image]'"
                        :image-deleted-name="'sections[' + index + '][image_deleted]'"
                        :is-required="true"
                        :errors="errors"
                        :init-data="section.hasOwnProperty('section_image_url') ? section.section_image_url : null"
                    />

                    <hr class="my-4">
                    <p><strong>{{ $t('admin.service_page_seo') }}</strong></p>

                    <multi-language-input-component
                        :title="$t('admin.meta_title')"
                        :name="'sections[' + index + '][meta_title]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="false"
                        :init-data="section.hasOwnProperty('meta_title') && section.meta_title !== null ? section.meta_title : []"
                        :errors="errors"
                    />

                    <multi-language-input-component
                        :title="$t('admin.meta_description')"
                        :name="'sections[' + index + '][meta_description]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="false"
                        :init-data="section.hasOwnProperty('meta_description') && section.meta_description !== null ? section.meta_description : []"
                        :errors="errors"
                    />

                    <multi-language-input-component
                        :title="$t('admin.meta_keywords')"
                        :name="'sections[' + index + '][meta_keywords]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="false"
                        :init-data="section.hasOwnProperty('meta_keywords') && section.meta_keywords !== null ? section.meta_keywords : []"
                        :errors="errors"
                    />

                    <div class="form-group mb-3">
                        <label :for="'service-meta-tags-' + index">{{ $t('admin.meta_tags') }}</label>
                        <textarea
                            class="form-control"
                            :id="'service-meta-tags-' + index"
                            :name="'sections[' + index + '][meta_tags]'"
                            rows="3"
                            :value="section.meta_tags || ''"
                        ></textarea>
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="d-flex flex-wrap align-items-center" style="gap: .5rem">
                        <button type="button" class="btn mb-2 btn-outline-secondary" @click="$emit('moveSection', -1)" :disabled="index === 0" :aria-label="$t('admin.move_up')">↑</button>
                        <button type="button" class="btn mb-2 btn-outline-secondary" @click="$emit('moveSection', 1)" :disabled="isLast" :aria-label="$t('admin.move_down')">↓</button>
                        <a href="#" class="btn mb-2 btn-danger" @click.prevent="() => $emit('deleteSection', index)"><span class="fe fe-trash fe-16 mr-2"></span>{{ $t('admin.section_delete')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
