<script>
import axios from "axios";

export default {
    components: {},
    props: {
        galleryId: {
            type: Number,
            default: null,
        },
        singleItem: {
            type: Object,
            default: {},
        },
        index: {
            type: Number,
            default: 0,
        },

        selectedColor: Number,
        allColors: {
            type: Array,
            default: [],
        },

        errors: {
            type: Object,
            default: [],
        }
    },
    emits: [
        'deleteGalleryItem',
    ],
    data () {
        return {
            slideDescription: [],
        }
    },
}
</script>

<template>
    <div class="art-repeater-row" :key="galleryId">
        <div class="col">
            <div class="row">
                <div class="col">
                    <input type="hidden" v-if="galleryId !== null" :name="'gallery[' + index + '][id]'" :value="galleryId">

                    <image-file-input-component
                        :title="$t('admin.gallery_image')"
                        :name="'gallery[' + index + '][image]'"
                        :image-deleted-name="'gallery[' + index + '][image_deleted]'"
                        :is-required="true"
                        :errors="errors"
                        :init-data="singleItem.hasOwnProperty('gallery_image_url') ? singleItem.gallery_image_url : null"
                    />

                    <div class="mb-3">
                        <select-component
                            :is-multi-select="false"
                            :model-value="selectedColor"
                            :title="$t('admin.product_colors')"
                            :options="allColors"
                            label="text"
                            value-prop="id"
                            :name="'gallery_color_ids[' + index + '][color_id]'"
                            :is-required="false"
                            :errors="errors"
                        />
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col">
                    <a href="#" id="add-option" class="btn mb-2 btn-danger" @click.prevent="() => $emit('deleteGalleryItem', index)"><span class="fe fe-trash fe-16 mr-2"></span>{{ $t('admin.slide_delete')}}</a>
                </div>
            </div>
        </div>
    </div>
</template>
