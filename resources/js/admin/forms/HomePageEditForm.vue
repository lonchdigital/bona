<script>

import axios from "axios";
import HomePageSlideComponent from "../components/HomePageSlideComponent.vue";

export default {
    components: {HomePageSlideComponent},
    props: {
        submitRoute: {
            type: String,
            default: '',
        },
        backRoute: {
            type: String,
            default: '',
        },
        collectionSearchRoute: {
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
        sliderSelectedCollection: {
            type: Object,
            default: null,
        },
        sliderLogo: {
            type: String,
            default: null,
        },
        sliderSlides: {
            type: Array,
            default: [],
        },
        availableBrands: {
            type: Array,
            default: [],
        },
        selectedBrands: {
            type: Array,
            default: [],
        },
        wallpapersByFieldId: {
            type: Number,
            default: null,
        },
        wallpapersFields: {
            type: Array,
            default: [],
        },
        selectedProductFieldOptions: {
            type: Array,
            default: [],
        },
        sliderTitle: {
            type: Object,
            default: {},
        }
    },
    data() {
        return {
            slides: [],
            selectedLanguage: '',
            collections: [],
            selectedFieldId: null,
            selectedOptions: [],
            errors: [],
        }
    },
    mounted() {
        this.selectedLanguage = this.baseLanguage;

        if (this.sliderSlides) {
            this.slides = this.sliderSlides;
        }

        if (this.sliderSelectedCollection && this.sliderSelectedCollection.hasOwnProperty('id') && this.sliderSelectedCollection.hasOwnProperty('name') ) {
            this.collections.push({id: this.sliderSelectedCollection.id, text: this.sliderSelectedCollection.name[this.selectedLanguage]});
        } else {
            this.loadCollections('');
        }

        if (this.wallpapersByFieldId) {
            this.selectedFieldId = this.wallpapersByFieldId;
        }
    },
    computed: {
        availableProductFieldOptions() {
            if(this.selectedFieldId) {
                const selectedWallpapersField = this.wallpapersFields.find((field) => field.id === this.selectedFieldId);
                if (selectedWallpapersField && selectedWallpapersField.hasOwnProperty('options')) {
                    return selectedWallpapersField.options;
                }
            }
            return [];
        }
    },
    watch: {
        selectedFieldId() {
            this.selectedOptions = [];
        }
    },
    methods: {
        addSlide() {
            this.slides.push({});
        },
        deleteSlide(index) {
            this.slides.splice(index, 1);
        },
        changeSelectedLanguage(newSelectedLanguage) {
            this.selectedLanguage = newSelectedLanguage;
        },
        handleFormSubmit(errors) {
            this.errors = errors;
        },
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
    <reactive-form-container
        :submit-route="submitRoute"
        :back-route="backRoute"
        @on-selected-language-change="changeSelectedLanguage"
        @on-errors-change="handleFormSubmit"
        :card-body-title="$t('admin.home_page_information') "
    >
        <div class="row">
            <div class="col">
                <!-- featured collections slider start -->
                <p>
                    <strong>
                        {{ $t('admin.slider') }}
                    </strong>
                </p>

                <multi-language-input-component
                    :title="$t('admin.slider_title')"
                    name="slider_title"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="true"
                    :init-data="sliderTitle"
                    :errors="errors"
                />

                <select-component
                    :model-value="sliderSelectedCollection && sliderSelectedCollection.hasOwnProperty('id') ? sliderSelectedCollection.id : null"
                    :title="$t('admin.collection_on_slider')"
                    :options="collections"
                    label="text"
                    value-prop="id"
                    name="collection_id"
                    @search-change="(query) => loadCollections(query)"
                    :is-required="true"
                    :errors="errors"
                />

                <image-file-input-component
                    :title="$t('admin.slider_logo')"
                    name="slider_logo"
                    image-deleted-name="slider_logo_deleted"
                    :is-required="true"
                    :errors="errors"
                    :init-data="sliderLogo"
                />

                <p>
                    <strong>
                        {{ $t('admin.slides') }}
                    </strong>
                </p>

                <home-page-slide-component
                    v-for="(slide, index) in slides"
                    :slide-id="slide.hasOwnProperty('id') ? slide.id : null"
                    :slide="slide"
                    :index="index"
                    :base-language="baseLanguage"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :errors="errors"
                    @delete-slide="() => deleteSlide(index)"
                    :key="'blog-slide-' + index"
                />

                <div class="row">
                    <div class="col">
                        <a href="#" id="add-option" class="btn mb-2 btn-secondary" @click.prevent="addSlide"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.slide_add')}}</a>
                    </div>
                </div>

                <!-- featured collections slider end -->

                <!-- featured brands start -->
                <p class="mt-3">
                    <strong>
                        {{ $t('admin.brands') }}
                    </strong>
                </p>

                <select-component
                    :is-multi-select="true"
                    :model-value="selectedBrands"
                    :title="$t('admin.brands')"
                    :options="availableBrands"
                    label="text"
                    value-prop="id"
                    name="selected_brands_id"
                    :max-items="6"
                    :is-required="true"
                    :errors="errors"
                />
                <!-- featured brands end -->

                <!-- featured products by custom option start -->
                <p class="mt-3">
                    <strong>
                        {{ $t('admin.products_by_field') }}
                    </strong>
                </p>

                <select-component
                    :model-value="selectedFieldId"
                    @model-value:update="selectedFieldId = $event"
                    :title="$t('admin.field') + ' (' + $t('admin.products_by_field_explanation') + ')'"
                    :options="wallpapersFields"
                    label="text"
                    value-prop="id"
                    name="selected_field_id"
                    :is-required="true"
                    :errors="errors"
                />

                <select-component
                    :is-multi-select="true"
                    :model-value="selectedProductFieldOptions"
                    :title="$t('admin.field_options')"
                    :options="availableProductFieldOptions"
                    label="text"
                    value-prop="id"
                    name="selected_field_options_id"
                    :key="'field_options_' + selectedFieldId"
                    :is-required="true"
                    :max-items="5"
                    :errors="errors"
                />
                <!-- featured products by custom option end -->

            </div>
        </div>
    </reactive-form-container>
</template>
