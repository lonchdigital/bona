<script>
import axios from "axios";
import MultiLanguageInputComponent from "./MultiLanguageInputComponent.vue";
import MultiLanguageTextAreaComponent from "./MultiLanguageTextAreaComponent.vue";

export default {
    components: {MultiLanguageInputComponent, MultiLanguageTextAreaComponent},
    props: {
        videoId: {
            type: Number,
            default: null,
        },
        video: {
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
        'deleteVideo',
    ],
    data () {
        return {
            slideDescription: [],
        }
    }
}
</script>

<template>
    <div class="row1 art-repeater-row" :key="videoId">
        <div class="col">
            <div class="row">
                <div class="col">
                    <input type="hidden" v-if="videoId !== null" :name="'videos[' + index + '][id]'" :value="videoId" >

                    <multi-language-input-component
                        :title="'Tab'"
                        :name="'videos[' + index + '][tab]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="true"
                        :init-data="video.hasOwnProperty('tab') ? video.tab : []"
                        :errors="errors"
                    />

                    <input-component
                        :title="'iframe'"
                        :name="'videos[' + index + '][iframe]'"
                        :model-value="video.iframe"
                        :errors="errors"
                        :is-required="true"
                    />

                </div>

            </div>
            <div class="row mt-3">
                <div class="col">
                    <a href="#" id="add-option" class="btn mb-2 btn-danger" @click.prevent="() => $emit('deleteVideo', index)"><span class="fe fe-trash fe-16 mr-2"></span>{{ $t('admin.video_delete')}}</a>
                </div>
            </div>
        </div>
    </div>
</template>
