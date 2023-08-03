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
    <div class="row">
        <div class="col">
            <div class="row">
                <div class="col">
                    <input type="hidden" v-if="slideId !== null" :name="'slides[' + index + '][id]'" :value="slideId">


                    <multi-language-input-component
                        :title="$t('admin.slide_description')"
                        :name="'slides[' + index + '][description]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="true"
                        :init-data="slide.hasOwnProperty('description') ? slide.description : []"
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
