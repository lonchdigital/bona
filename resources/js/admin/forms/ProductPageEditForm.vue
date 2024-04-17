<script>

import axios from "axios";
import SelectComponent from "../components/SelectComponent.vue";
import SelectColorComponent from "../components/SelectColorComponent.vue";
import MultiLanguageInputComponent from "../components/MultiLanguageInputComponent.vue";
import MultiLanguageRichTextEditorComponent from "../components/MultiLanguageRichTextEditorComponent.vue";
import ProductGalleryComponent from "../components/ProductGalleryComponent.vue";
import HomePageFaqComponent from "../components/HomePageFaqComponent.vue";
import ProductCharacteristicsComponent from "../components/ProductCharacteristicsComponent.vue";
import ProductVideosComponent from "../components/ProductVideosComponent.vue";
import ImageFileInputComponent from "../components/ImageFileInputComponent.vue";
import ProductAttributesComponent from "../components/ProductAttributesComponent.vue";
import TextAreaComponent from "../components/TextAreaComponent.vue";
import * as transliteration from 'transliteration';


export default {
    components: {
        MultiLanguageRichTextEditorComponent,
        MultiLanguageInputComponent,
        SelectComponent,
        SelectColorComponent,
        ProductGalleryComponent,
        ProductCharacteristicsComponent,
        ProductVideosComponent,
        ImageFileInputComponent,
        ProductAttributesComponent,
        HomePageFaqComponent,
        TextAreaComponent
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
        subProductSearchRoute: {
            type: String,
            default: '',
        },

        productName: {
            type: Object,
            default: {},
        },
        productSku: {
            type: String,
            default: '',
        },
        productSlug: {
            type: String,
            default: '',
        },
        productMetaTitle: {
            type: Object,
            default: {},
        },
        productMetaDescription: {
            type: Object,
            default: {},
        },
        productMetaKeywords: {
            type: Object,
            default: {},
        },
        productMetaTags: {
            type: String,
            default: '',
        },
        selectedSubProducts: {
            type: Array,
            default: [],
        },
        availabilityStatusOptions: {
            type: Object,
            default: {},
        },
        availabilityStatusOptionsSelected: {
            type: Number,
            default: null,
        },
        oldPrice: {
            type: Number,
            default: null,
        },
        price: {
            type: Number,
            default: null,
        },
        purchasePriceInCurrency: {
            type: Number,
            default: null,
        },
        currencyOptions: {
            type: Object,
            default: {},
        },
        currencySelected: {
            type: Number,
            default: null,
        },
        categoryDisplay: {
            type: Number,
            default: false,
        },
        categoryOptions: {
            type: Object,
            default: {},
        },
        categorySelected: {
            type: Number,
            default: null,
        },

        brandDisplay: {
            type: Number,
            default: false,
        },
        brandOptions: {
            type: Object,
            default: {},
        },
        brandSelected: {
            type: Number,
            default: null,
        },

        colorOptions: {
            type: Array,
            default: [],
        },
        colorSelected: {
            type: Array,
            default: [],
        },
        colorDisplay: {
            type: Number,
            default: false,
        },
        productMainImage: {
            type: String,
            default: '',
        },
        productGallery: {
            type: Array,
            default: [],
        },
        productText: {
            type: Object,
            default: [],
        },
        productCharacteristics: {
            type: Array,
            default: [],
        },
        productVideos: {
            type: Array,
            default: [],
        },
        productFaqs: {
            type: Array,
            default: [],
        },

        productCustomFields: {
            type: Object,
            default: {},
        },

        productCustomAttributes: {
            type: Object,
            default: {},
        },
        productAttributeOptions: {
            type: Object,
            default: {},
        },
        seoTitle: {
            type: Object,
            default: [],
        },
        seoText: {
            type: Object,
            default: [],
        }

    },
    data() {
        return {
            gallery: [],
            characteristics: [],
            videos: [],
            selectedLanguage: '',
            selectedFieldId: null,
            // availabilityStatusArray: {},
            selectedSubProductsShow: [],
            selectedColorsShow: [],
            colors: [],
            products: [],
            selectedOptions: [],
            errors: [],
            displayCategoryField: [],
            displayBrandField: [],
            displayColorField: [],
            attributeOptions: [],
            productSlugData: '',
            faqDeleted: false,
            faqs: [],
        }
    },
    created() {
        // this.availabilityStatusArray = Object.values(this.availabilityStatusOptions); // преобразуем объект в массив
    },
    mounted() {
        this.selectedLanguage = this.baseLanguage;
        this.productSlugData = this.productSlug;

        if (this.productCharacteristics) {
            this.characteristics = this.productCharacteristics;
        }
        if (this.productVideos) {
            this.videos = this.productVideos;
        }
        if (this.productFaqs) {
            this.faqs = this.productFaqs;
        }
        /*if (this.colorOptions) {
            this.colors = this.colorOptions;
        }*/
        if (this.productAttributeOptions) {
            this.attributeOptions = this.productAttributeOptions;
        }
        if (this.productGallery) {
            this.gallery = this.productGallery;
        }

        this.displayCategoryField = this.categoryDisplay;
        this.displayBrandField = this.brandDisplay;
        this.displayColorField = this.colorDisplay;


        this.loadProducts('');

        if( Array.isArray(this.selectedSubProducts) ) {
            this.selectedSubProducts.forEach((item, i) => {
                if (item && item.hasOwnProperty('id') && item.hasOwnProperty('name')) {
                    this.selectedSubProductsShow.push(item.id);
                    this.products.push({id: item.id, text: item.name[this.selectedLanguage] + ' ' + item.sku});
                } else {
                    this.loadProducts('');
                }
            });
        }


        if( Array.isArray(this.colorSelected) ) {
            this.colorSelected.forEach((item, i) => {
                if (item && item.hasOwnProperty('id') && item.hasOwnProperty('name')) {
                    this.selectedColorsShow.push({id: item.id, price: item.pivot.price});
                }
            });
        }
        if( Array.isArray(this.colorOptions) ) {
            this.colorOptions.forEach((item, i) => {
                if (item && item.hasOwnProperty('id') && item.hasOwnProperty('name')) {
                    this.colors.push({id: item.id, text: item.name[this.selectedLanguage]});
                }
            });
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
        deleteGalleryItem(index) {
            this.gallery.splice(index, 1);
        },
        addGalleryItem() {
            this.gallery.push({});
        },
        addColor() {
            this.selectedColorsShow.push({});
        },
        deleteColor(index) {
            this.selectedColorsShow.splice(index, 1);
        },
        deleteCharacteristic(index) {
            this.characteristics.splice(index, 1);
        },
        addCharacteristic() {
            this.characteristics.push({});
        },
        addAttribute(attribute) {
            if (Array.isArray(this.attributeOptions[attribute.id])) {
                this.attributeOptions[attribute.id].push({});
            } else {
                this.attributeOptions[attribute.id] = [];
                this.attributeOptions[attribute.id].push({});
            }
        },
        deleteAttribute(attribute, index, attributeOptionId) {

            if (Array.isArray(this.attributeOptions[attribute.product_attribute_id])) {
                this.attributeOptions[attribute.product_attribute_id].splice(index, 1);
            } else {
                this.attributeOptions[attributeOptionId].splice(index, 1);
            }

        },
        deleteVideo(index) {
            this.videos.splice(index, 1);
        },
        addVideo() {
            this.videos.push({});
        },
        handleFormSubmit(errors) {
            this.errors = errors;
        },
        loadProducts(query) {
            axios.get(this.subProductSearchRoute + '?query=' + query).then((result) => {
                this.products = result.data.data;
            }).catch(() => {
                this.products = [];
            });
        },
        updateSlug(value) {
            if(this.selectedLanguage === 'uk'){
                value = event.target.value;
                this.productSlugData = transliteration.slugify(value);
            }
        },
        addFaq() {
            this.faqs.push({});
        },
        deleteFaq(index) {
            this.faqs.splice(index, 1);
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
        :card-body-title="$t('admin.product_information') "
    >
        <div class="row">
            <div class="col">

                <!--PARENT PRODUCT miss-->

                <multi-language-input-component
                    :title="$t('admin.name')"
                    name="name"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="true"
                    :init-data="productName"
                    :errors="errors"
                    @input="updateSlug"
                />

                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.sku')"
                        :name="'sku'"
                        :model-value="productSku"
                        :errors="errors"
                        :is-required="false"
                    />
                </div>

                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.slug')"
                        :name="'slug'"
                        :model-value="productSlugData"
                        :errors="errors"
                        :is-required="true"
                    />
                </div>

                <multi-language-input-component
                    :title="$t('admin.meta_title')"
                    name="meta_title"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="productMetaTitle"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.meta_description')"
                    name="meta_description"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="productMetaDescription"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.meta_keywords')"
                    name="meta_keywords"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="productMetaKeywords"
                    :errors="errors"
                />

                <div class="form-group mb-3">
                    <div class="art-info-field">
                        <p>Available tags: %title%, %price%, %product_type%</p>
                    </div>
                </div>


                <div class="form-group mb-3 art-multiselect-height">
                    <select-component
                        :is-multi-select="true"
                        :model-value="selectedSubProductsShow"
                        :title="$t('admin.product_subtypes')"
                        :options="products"
                        label="text"
                        value-prop="id"
                        name="selected_sub_products_id"
                        :max-items="99"
                        @search-change="(query) => loadProducts(query)"
                        :is-required="false"
                        :errors="errors"
                    />
                </div>

                <div class="form-group mb-3">
                    <select-component
                        :title="$t('admin.availability_status')"
                        :options="availabilityStatusOptions"
                        :model-value="availabilityStatusOptionsSelected"
                        label="name"
                        value-prop="availability_status_id"
                        name="availability_status_id"
                        :is-required="false"
                        :errors="errors"
                    />
                </div>

                <!--SPECIAL OFFERS miss-->

                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.old_price')"
                        :type="'number'"
                        :name="'old_price'"
                        :model-value="oldPrice"
                        :errors="errors"
                        :is-required="false"
                    />
                </div>

                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.price')"
                        :type="'number'"
                        :name="'price'"
                        :model-value="price"
                        :errors="errors"
                        :is-required="true"
                    />
                </div>

                <div class="form-group mb-3 art-form-group-hidden">
                    <input-component
                        :title="$t('admin.purchase_price_in_currency')"
                        :type="'number'"
                        :name="'purchase_price_in_currency'"
                        :model-value="purchasePriceInCurrency"
                        :errors="errors"
                        :is-required="true"
                    />
                </div>

                <div class="form-group mb-3 art-form-group-hidden">
                    <select-component
                        :title="$t('admin.price_currency')"
                        :options="currencyOptions"
                        :model-value="currencySelected"
                        label="currency"
                        value-prop="currency_id"
                        name="currency_id"
                        :is-required="true"
                        :errors="errors"
                    />
                </div>


                <div class="form-group mb-3" v-if="displayCategoryField">
                    <select-component
                        :title="$t('admin.product_categories')"
                        :options="categoryOptions"
                        :model-value="categorySelected"
                        label="category"
                        value-prop="category_ids"
                        name="category_ids[]"
                        :is-required="true"
                        :errors="errors"
                    />
                </div>

                <div class="form-group mb-3" v-if="displayBrandField">
                    <select-component
                        :title="$t('admin.product_brand')"
                        :options="brandOptions"
                        :model-value="brandSelected"
                        label="brand"
                        value-prop="brand_id"
                        name="brand_id"
                        :is-required="false"
                        :errors="errors"
                    />
                </div>


                <p class="mt-4" v-if="displayColorField">
                    <strong>
                        {{ $t('admin.color') }}
                    </strong>
                </p>
                <div class="form-group mb-3 art-admin-repeater-four-width" v-if="displayColorField">
                    <!--                    <select-component
                                            :is-multi-select="true"
                                            :model-value="selectedColorsShow"
                                            :title="$t('admin.product_colors')"
                                            :options="colors"
                                            label="text"
                                            value-prop="id"
                                            name="all_color_ids"
                                            :is-required="false"
                                            :errors="errors"
                                        />-->

                    <select-color-component
                        v-for="(selectedColor, index) in selectedColorsShow"

                        :selected-color="selectedColor"
                        :all-colors="colors"
                        :index="index"
                        :base-language="baseLanguage"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :errors="errors"
                        @delete-color="() => deleteColor(index)"
                    />
                </div>
                <div class="row mb-3" v-if="displayColorField">
                    <div class="col">
                        <a href="#" id="add-color-option" class="btn mb-2 btn-secondary" @click.prevent="addColor"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.color_add')}}</a>
                    </div>
                </div>


                <div class="form-group mb-3">
                    <image-file-input-component
                        :title="$t('admin.product_main_image') + ' ' + $t('admin.product_main_image_requirements')"
                        name="main_image"
                        image-deleted-name="main_image_deleted_input"
                        :is-required="false"
                        :errors="errors"
                        :init-data="productMainImage"
                    />
                </div>

                <p class="mt-4">
                    <strong>
                        {{ $t('admin.product_gallery') }}
                    </strong>
                </p>

                <div class="form-group mb-3 art-admin-product-gallery">
                    <product-gallery-component
                        v-for="(item, index) in gallery"
                        :gallery-id="item.hasOwnProperty('id') ? item.id : null"
                        :single-item="item"
                        :index="index"
                        :errors="errors"
                        @delete-gallery-item="() => deleteGalleryItem(index)"
                    />
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <a href="#" id="add-option" class="btn mb-2 btn-secondary" @click.prevent="addGalleryItem"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.add_image')}}</a>
                    </div>
                </div>


                <multi-language-rich-text-editor-component
                    :title="$t('admin.product_text')"
                    name="product_text"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :content="productText"
                    :is-required="false"
                    :errors="errors"
                />


                <p class="mt-4">
                    <strong>
                        {{ $t('admin.product_characteristics') }}
                    </strong>
                </p>

                <div class="form-group mb-3 art-admin-repeater-four-width">
                    <product-characteristics-component
                        v-for="(characteristic, index) in characteristics"
                        :characteristic-id="characteristic.hasOwnProperty('id') ? characteristic.id : null"
                        :characteristic="characteristic"
                        :index="index"
                        :base-language="baseLanguage"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :errors="errors"
                        @delete-characteristic="() => deleteCharacteristic(index)"
                    />
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <a href="#" id="add-characteristic-option" class="btn mb-2 btn-secondary" @click.prevent="addCharacteristic"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.characteristic_add')}}</a>
                    </div>
                </div>


                <p class="mt-4">
                    <strong>
                        {{ $t('admin.opening_systems') }}
                    </strong>
                </p>

                <div class="form-group mb-3 art-admin-repeater-four-width">
                    <product-videos-component
                        v-for="(video, index) in videos"
                        :video-id="video.hasOwnProperty('id') ? video.id : null"
                        :video="video"
                        :index="index"
                        :base-language="baseLanguage"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :errors="errors"
                        @delete-video="() => deleteVideo(index)"
                    />
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <a href="#" id="add-video-option" class="btn mb-2 btn-secondary" @click.prevent="addVideo"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.video_add')}}</a>
                    </div>
                </div>

                <div class="form-group mb-3" v-for="productCustomField in productCustomFields">
                    <select-component
                        :title="productCustomField.field_name[baseLanguage]"
                        :options="productCustomField.custom_options"

                        :model-value-selected="productCustomField.id"
                        :name-selected="'custom_field['+ productCustomField.id +'][field_id]'"

                        :model-value="productCustomField.custom_options_selected"
                        :name="'custom_field['+ productCustomField.id +'][value]'"

                        :label="'custom_field['+ productCustomField.id +'][field_id]'"
                        :is-required="true"
                        :errors="errors"
                    />
                </div>



                <div class="form-group mb-3" v-for="productCustomAttribute in productCustomAttributes">

                    <p class="mt-4">
                        <strong>
                            {{ productCustomAttribute.attribute_name[selectedLanguage] }}
                        </strong>
                    </p>

                    <div class="form-group mb-3 art-admin-repeater-four-width">
                        <product-attributes-component
                            v-for="(attribute, index) in attributeOptions[productCustomAttribute.id]"
                            :attribute-id="attribute.hasOwnProperty('id') ? attribute.id : null"
                            :attribute="attribute"
                            :attribute-option-id="productCustomAttribute.id"
                            :index="index"
                            :base-language="baseLanguage"
                            :selected-language="selectedLanguage"
                            :available-languages="availableLanguages"
                            :errors="errors"
                            @delete-attribute="() => deleteAttribute(attribute, index, productCustomAttribute.id)"
                        />
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <a href="#" id="add-attribute-option-{{ productCustomAttribute.slug }}" class="btn mb-2 btn-secondary" @click.prevent="addAttribute(productCustomAttribute)"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.attribute_add')}}</a>
                        </div>
                    </div>

                </div>



                <p class="mt-4">
                    <strong>
                        {{ $t('admin.questions') }}
                    </strong>
                </p>
                <div class="form-group mb-3 art-admin-repeater-four-width">
                    <home-page-faq-component
                        v-for="(faq, index) in faqs"
                        :faq-id="faq.hasOwnProperty('id') ? faq.id : null"
                        :faq="faq"
                        :index="index"
                        :base-language="baseLanguage"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :errors="errors"
                        :faq-deleted="faqDeleted"
                        @delete-faq="() => deleteFaq(index)"
                    />
                </div>
                <div class="row">
                    <div class="col">
                        <a href="#" id="add-faq-option" class="btn mb-2 btn-secondary" @click.prevent="addFaq"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.question_add')}}</a>
                    </div>
                </div>


                <p class="mt-4">
                    <strong>
                        {{ $t('admin.seo') }}
                    </strong>
                </p>
                <multi-language-input-component
                    :title="$t('admin.seo_title')"
                    name="seo_title"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="seoTitle"
                    :errors="errors"
                />
                <multi-language-rich-text-editor-component
                    :title="$t('admin.seo_text')"
                    name="seo_text"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :content="seoText"
                    :errors="errors"
                />

                <text-area-component
                    :title="$t('admin.meta_tags')"
                    name="meta_tags"
                    :is-required="false"
                    :init-data="productMetaTags"
                    :errors="errors"
                />

            </div>
        </div>
    </reactive-form-container>
</template>
