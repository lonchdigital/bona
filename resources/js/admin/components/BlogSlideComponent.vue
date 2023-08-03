<script>
import axios from "axios";

export default {
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
        collectionSearchRoute: {
            type: String,
            default: '',
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
            collections: [],
            slideDescription: [],
            selectedCollection: null,
        }
    },
    beforeMount() {
        if (this.slide.hasOwnProperty('collection_id')) {
            this.collections.push({id: this.slide.collection_id, text: this.slide.collection.name[this.baseLanguage]});
        } else {
            this.loadCollections('');
        }
    },
    methods: {
        loadCollections(query) {
            axios.get(this.collectionSearchRoute + '?query=' + query).then((result) => {
                this.collections = result.data.data;
            }).catch(() => {
                this.collections = [];
            });
        },
    }
}
</script>

<template>
    <div class="row">
        <div class="col">
            <div class="row">
                <div class="col">
                    <input type="hidden" v-if="slideId !== null" :name="'slides[' + index + '][id]'" :value="slideId">

                    <!-- collection select-->
                    <select-component
                        :model-value="this.slide.collection_id"
                        :title="$t('admin.collection_on_slide')"
                        :options="collections"
                        label="text"
                        value-prop="id"
                        :name="'slides[' + index + '][collection_id]'"
                        @search-change="(query) => loadCollections(query)"
                        :is-required="true"
                        :errors="errors"
                    />

                    <multi-language-text-area-component
                        :title="$t('admin.slide_description')"
                        :name="'slides[' + index + '][description]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="true"
                        :init-data="slide.hasOwnProperty('description') ? slide.description : []"
                        :errors="errors"
                    />

                    <image-file-input-component
                        :title="$t('admin.slide_image_1')"
                        :name="'slides[' + index + '][image_1]'"
                        :image-deleted-name="'slides[' + index + '][image_1_deleted]'"
                        :is-required="true"
                        :errors="errors"
                        :init-data="slide.hasOwnProperty('slide_image_1_url') ? slide.slide_image_1_url : null"
                    />

                    <image-file-input-component
                        :title="$t('admin.slide_image_2')"
                        :name="'slides[' + index + '][image_2]'"
                        :image-deleted-name="'slides[' + index + '][image_2_deleted]'"
                        :is-required="true"
                        :errors="errors"
                        :init-data="slide.hasOwnProperty('slide_image_2_url') ? slide.slide_image_2_url : null"
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
