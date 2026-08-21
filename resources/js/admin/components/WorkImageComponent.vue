<script>
import MultiLanguageInputComponent from "./MultiLanguageInputComponent.vue";
import ImageFileInputComponent from "./ImageFileInputComponent.vue";

export default {
    components: {MultiLanguageInputComponent, ImageFileInputComponent},
    props: {
        imageId: {
            type: Number,
            default: null,
        },
        image: {
            type: Object,
            default: {},
        },
        index: {
            type: Number,
            default: 0,
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
        },
    },
    emits: [
        'deleteImage',
    ],
}
</script>

<template>
    <div class="row1 art-repeater-row" :key="imageId">
        <div class="col">

            <input type="hidden" v-if="imageId !== null" :name="'work_image[' + index + '][id]'" :value="imageId">

            <div class="form-group mb-3">
                <image-file-input-component
                    :title="$t('admin.work_gallery')"
                    :name="'work_image[' + index + '][image]'"
                    :is-required="imageId === null"
                    :errors="errors"
                    :init-data="image.hasOwnProperty('image_url') ? image.image_url : null"
                />
            </div>

            <multi-language-input-component
                :title="$t('admin.work_gallery_caption')"
                :name="'work_image[' + index + '][caption]'"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="false"
                :init-data="image.hasOwnProperty('caption') ? image.caption : []"
                :errors="errors"
            />

            <div class="row">
                <div class="col">
                    <a href="#" class="btn mb-2 btn-danger" @click.prevent="() => $emit('deleteImage', index)">
                        <span class="fe fe-trash fe-16 mr-2"></span>{{ $t('admin.delete') }}
                    </a>
                </div>
            </div>

        </div>
    </div>
</template>
