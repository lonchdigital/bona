<script>

import axios from "axios";
import HomePageSlideComponent from "../components/HomePageSlideComponent.vue";
import HomePageTestimonialComponent from "../components/HomePageTestimonialComponent.vue";
import HomePageFaqComponent from "../components/HomePageFaqComponent.vue";
import MultiLanguageRichTextEditorComponent from "../components/MultiLanguageRichTextEditorComponent.vue";
import MultiLanguageInputComponent from "../components/MultiLanguageInputComponent.vue";
import TextAreaComponent from "../components/TextAreaComponent.vue";
import SelectComponent from "../components/SelectComponent.vue";

export default {
    components: {MultiLanguageRichTextEditorComponent,
        HomePageSlideComponent,
        HomePageTestimonialComponent,
        HomePageFaqComponent,
        MultiLanguageInputComponent,
        TextAreaComponent,
        SelectComponent
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
        collectionSearchRoute: {
            type: String,
            default: '',
        },
        productSearchRoute: {
            type: String,
            default: '',
        },
        brandSearchRoute: {
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
        pageMetaTitle: {
            type: Object,
            default: {},
        },
        pageMetaDescription: {
            type: Object,
            default: {},
        },
        pageMetaKeywords: {
            type: Object,
            default: {},
        },
        productMetaTags: {
            type: String,
            default: '',
        },
        sliderLogo: {
            type: String,
            default: null,
        },
        sliderSlides: {
            type: Array,
            default: [],
        },
        testimonialList: {
            type: Array,
            default: [],
        },
        faqList: {
            type: Array,
            default: [],
        },
        availableProducts: {
            type: Array,
            default: [],
        },
        allProductTypes: {
            type: Array,
            default: [],
        },
        selectedProductTypes: {
            type: Array,
            default: [],
        },
        selectedNewProducts: {
            type: Array,
            default: [],
        },
        selectedBestSalesProducts: {
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
        testimonialsRatingOptions: {
            type: Object,
            default: {},
        },
        selectedProductFieldOptions: {
            type: Array,
            default: [],
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
            slides: [],
            testimonials: [],
            faqs: [],
            faqDeleted: false,
            test: [],
            selectedLanguage: '',
            collections: [],
            productTypes: [],
            products: [],
            brands: [],
            selectedNewProductsShow: [],
            selectedBestSalesProductsShow: [],
            selectedBrandsShow: [],
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

        if (this.testimonialList) {
            this.testimonials = this.testimonialList;
        }

        if (this.faqList) {
            this.faqs = this.faqList;
        }

        this.loadProducts('');
        this.loadBrands('');

        if (this.wallpapersByFieldId) {
            this.selectedFieldId = this.wallpapersByFieldId;
        }


        if( Array.isArray(this.allProductTypes) ) {
            this.allProductTypes.forEach((item, i) => {
                if (item && item.hasOwnProperty('id') && item.hasOwnProperty('name')) {
                    this.productTypes.push({id: item.id, text: item.name[this.selectedLanguage]});
                }
            });
        }

        if( Array.isArray(this.selectedNewProducts) ) {
            this.selectedNewProducts.forEach((item, i) => {
                if (item.product && item.product.hasOwnProperty('id') && item.product.hasOwnProperty('name')) {
                    this.selectedNewProductsShow.push(item.product.id);
                    this.products.push({id: item.product.id, text: item.product.name[this.selectedLanguage] + ' ' + item.product.sku});
                } else {
                    this.loadProducts('');
                }
            });
        }

        if( Array.isArray(this.selectedBestSalesProducts) ) {
            this.selectedBestSalesProducts.forEach((item, i) => {
                if (item.product && item.product.hasOwnProperty('id') && item.product.hasOwnProperty('name')) {
                    this.selectedBestSalesProductsShow.push(item.product.id);
                    this.products.push({id: item.product.id, text: item.product.name[this.selectedLanguage] + ' ' + item.product.sku});
                } else {
                    this.loadProducts('');
                }
            });
        }

        if( Array.isArray(this.selectedBrands) ) {
            this.selectedBrands.forEach((item, i) => {
                if (item.brand && item.brand.hasOwnProperty('id') && item.brand.hasOwnProperty('name')) {
                    this.selectedBrandsShow.push(item.brand.id);
                    this.brands.push({id: item.brand.id, text: item.brand.name[this.selectedLanguage]});
                } else {
                    this.loadBrands('');
                }

            });
        }

    },
    /*computed: {
        availableProductFieldOptions() {
            if(this.selectedFieldId) {
                const selectedWallpapersField = this.wallpapersFields.find((field) => field.id === this.selectedFieldId);
                if (selectedWallpapersField && selectedWallpapersField.hasOwnProperty('options')) {
                    return selectedWallpapersField.options;
                }
            }
            return [];
        }
    },*/
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
        addTestimonial() {
            this.testimonials.push({});
        },
        deleteTestimonial(index) {
            this.testimonials.splice(index, 1);
        },
        addFaq() {
            this.faqs.push({});
        },
        deleteFaq(index) {
            this.faqs.splice(index, 1);
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
        loadProducts(query) {
            axios.get(this.productSearchRoute + '?query=' + query).then((result) => {
                this.products = result.data.data;
            }).catch(() => {
                this.products = [];
            });
        },
        loadBrands(query) {
            axios.get(this.brandSearchRoute + '?query=' + query).then((result) => {
                this.brands = result.data.data;
            }).catch(() => {
                this.brands = [];
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

                <multi-language-input-component
                    :title="$t('admin.meta_title')"
                    name="meta_title"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="pageMetaTitle"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.meta_description')"
                    name="meta_description"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="pageMetaDescription"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.meta_keywords')"
                    name="meta_keywords"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="pageMetaKeywords"
                    :errors="errors"
                />


                <p>
                    <strong>
                        {{ $t('admin.slider') }}
                    </strong>
                </p>
                <div class="row">
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
                    />
                </div>
                <div class="row">
                    <div class="col">
                        <a href="#" id="add-option" class="btn mb-2 btn-secondary" @click.prevent="addSlide"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.slide_add')}}</a>
                    </div>
                </div>



                <p class="mt-4"></p>

                <select-component
                    :is-multi-select="true"
                    :model-value="selectedProductTypes"
                    :title="$t('admin.product_types')"
                    :options="productTypes"
                    label="text"
                    value-prop="id"
                    name="selected_product_types"
                    :max-items="20"
                    :is-required="false"
                    :errors="errors"
                />

                <p class="mt-4"></p>

                <select-component
                    :is-multi-select="true"
                    :model-value="selectedNewProductsShow"
                    :title="$t('admin.new_items')"
                    :options="products"
                    label="text"
                    value-prop="id"
                    name="selected_products_id"
                    :max-items="6"
                    @search-change="(query) => loadProducts(query)"
                    :is-required="false"
                    :errors="errors"
                />


                <p class="mt-4"></p>

                <select-component
                    :is-multi-select="true"
                    :model-value="selectedBestSalesProductsShow"
                    :title="$t('admin.new_best_sales_items')"
                    :options="products"
                    label="text"
                    value-prop="id"
                    name="selected_best_sales_products_id"
                    :max-items="6"
                    @search-change="(query) => loadProducts(query)"
                    :is-required="false"
                    :errors="errors"
                />




                <p class="mt-4">
                    <strong>
                        {{ $t('admin.testimonials') }}
                    </strong>
                </p>
                <div class="form-group mb-3 art-admin-repeater-four-width">
                    <home-page-testimonial-component
                        v-for="(testimonial, index) in testimonials"
                        :testimonial-id="testimonial.hasOwnProperty('id') ? testimonial.id : null"
                        :testimonial="testimonial"
                        :index="index"
                        :base-language="baseLanguage"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :rating-options="testimonialsRatingOptions"
                        :errors="errors"
                        @delete-testimonial="() => deleteTestimonial(index)"
                    />
                </div>
                <div class="row">
                    <div class="col">
                        <a href="#" id="add-testimonial-option" class="btn mb-2 btn-secondary" @click.prevent="addTestimonial"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.testimonial_add')}}</a>
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



                <p class="mt-4"></p>

                <select-component
                    :is-multi-select="true"
                    :model-value="selectedBrandsShow"
                    :title="$t('admin.brands')"
                    :options="brands"
                    label="text"
                    value-prop="id"
                    name="selected_brands_id"
                    :max-items="6"
                    @search-change="(query) => loadProducts(query)"
                    :is-required="false"
                    :errors="errors"
                />



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
