<script>
import axios from "axios";
import MultiLanguageInputComponent from "./MultiLanguageInputComponent.vue";

export default {
    components: {MultiLanguageInputComponent},
    props: {
        slideId: {
            type: Number,
            default: null,
        },
        slide: {
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
        'deleteSlide',
    ],
    data () {
        return {
            slideDescription: [],
        }
    },
}
</script>

<template>
    <div class="row art-repeater-row" :key="slideId">
        <div class="col">
            <div class="row">
                <div class="col">
                    <input type="hidden" v-if="slideId !== null" :name="'slides[' + index + '][id]'" :value="slideId">


                    <multi-language-input-component
                        :title="$t('admin.slide_title')"
                        :name="'slides[' + index + '][title]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="true"
                        :init-data="slide.hasOwnProperty('title') ? slide.title : []"
                        :errors="errors"
                    />

                    <multi-language-input-component
                        :title="$t('admin.slide_description')"
                        :name="'slides[' + index + '][description]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="false"
                        :init-data="(slide.hasOwnProperty('description') && slide.description !== null) ? slide.description : []"
                        :errors="errors"
                    />

                    <image-file-input-component
                        :title="$t('admin.slide_image')"
                        :name="'slides[' + index + '][image]'"
                        :image-deleted-name="'slides[' + index + '][image_deleted]'"
                        :is-required="true"
                        :errors="errors"
                        :init-data="slide.hasOwnProperty('slide_image_url') ? slide.slide_image_url : null"
                    />

                    <multi-language-input-component
                        :title="$t('admin.slide_text_button')"
                        :name="'slides[' + index + '][button_text]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="true"
                        :init-data="slide.hasOwnProperty('button_text') ? slide.button_text : []"
                        :errors="errors"
                    />

                    <div class="form-group mb-3">
                        <input-component
                            :title="$t('admin.slide_text_link')"
                            :name="'slides[' + index + '][button_url]'"
                            :model-value="slide.hasOwnProperty('button_url') ? slide.button_url : []"
                            :errors="errors"
                            :is-required="true"
                        />
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col">
                    <a href="#" id="add-option" class="btn mb-2 btn-danger" @click.prevent="() => $emit('deleteSlide', index)"><span class="fe fe-trash fe-16 mr-2"></span>{{ $t('admin.slide_delete')}}</a>
                </div>
            </div>
        </div>
    </div>
</template>
