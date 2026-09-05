<script>

import axios from "axios";
import HomePageSlideComponent from "../components/HomePageSlideComponent.vue";
import HomePageTestimonialComponent from "../components/HomePageTestimonialComponent.vue";
import HomePageFaqComponent from "../components/HomePageFaqComponent.vue";
import MultiLanguageRichTextEditorComponent from "../components/MultiLanguageRichTextEditorComponent.vue";
import MultiLanguageInputComponent from "../components/MultiLanguageInputComponent.vue";
import TextAreaComponent from "../components/TextAreaComponent.vue";
import SelectComponent from "../components/SelectComponent.vue";
import HomePageStyleItemComponent from "../components/HomePageStyleItemComponent.vue";
import HomePageSectionCardComponent from "../components/HomePageSectionCardComponent.vue";
import HomePageNumberItemComponent from "../components/HomePageNumberItemComponent.vue";
import HomePageStepItemComponent from "../components/HomePageStepItemComponent.vue";
import HomePageVisualItemComponent from "../components/HomePageVisualItemComponent.vue";

export default {
    components: {MultiLanguageRichTextEditorComponent,
        HomePageSlideComponent,
        HomePageTestimonialComponent,
        HomePageFaqComponent,
        MultiLanguageInputComponent,
        TextAreaComponent,
        SelectComponent,
        HomePageStyleItemComponent,
        HomePageSectionCardComponent,
        HomePageNumberItemComponent,
        HomePageStepItemComponent,
        HomePageVisualItemComponent,
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
        productSearchRoute: {
            type: String,
            default: '',
        },
        brandSearchRoute: {
            type: String,
            default: '',
        },
        instagramAuthRoute: {
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
        allCatalogOptions: {
            type: Array,
            default: [],
        },
        selectedProductTypes: {
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
        testimonialsRatingOptions: {
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
        },
        styleSection: {
            type: Object,
            default: () => ({}),
        },
        contentSections: {
            type: Object,
            default: () => ({}),
        }
    },
    data() {
        return {
            slides: [],
            testimonials: [],
            faqs: [],
            faqDeleted: false,
            selectedLanguage: '',
            catalogOptions: [],
            products: [],
            brands: [],
            selectedBestSalesProductsShow: [],
            selectedBrandsShow: [],
            styleItems: [],
            numberItems: [],
            ideaItems: [],
            stepItems: [],
            workItems: [],
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

        if (Array.isArray(this.styleSection.items)) {
            this.styleItems = this.styleSection.items.map(item => ({
                ...item,
                _editorKey: this.createEditorKey('style'),
            }));
        }

        this.numberItems = this.cloneSectionItems('numbers');
        this.ideaItems = this.cloneSectionItems('ideas');
        this.stepItems = this.cloneSectionItems('steps');
        this.workItems = this.cloneSectionItems('works');

        this.loadProducts('');
        this.loadBrands('');

        if( Array.isArray(this.allCatalogOptions) ) {
            this.allCatalogOptions.forEach((item, i) => {
                if (item && item.hasOwnProperty('id') && item.hasOwnProperty('name')) {
                    this.catalogOptions.push({id: item.id, text: item.name[this.selectedLanguage]});
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
        addStyleItem() {
            this.styleItems.push({ name: {}, _editorKey: this.createEditorKey('style') });
        },
        deleteStyleItem(index) {
            this.styleItems.splice(index, 1);
        },
        moveStyleItem(index, offset) {
            const targetIndex = index + offset;

            if (targetIndex < 0 || targetIndex >= this.styleItems.length) {
                return;
            }

            const [item] = this.styleItems.splice(index, 1);
            this.styleItems.splice(targetIndex, 0, item);
        },
        cloneSectionItems(section) {
            const items = this.contentSections?.[section]?.items;

            return Array.isArray(items) ? items.map(item => ({
                ...item,
                _editorKey: this.createEditorKey(section),
            })) : [];
        },
        createEditorKey(prefix) {
            const suffix = globalThis.crypto?.randomUUID?.()
                || `${Date.now()}-${Math.random().toString(36).slice(2)}`;

            return `${prefix}-${suffix}`;
        },
        addNumberItem() {
            this.numberItems.push({ value: '', label: {}, _editorKey: this.createEditorKey('numbers') });
        },
        addIdeaItem() {
            this.ideaItems.push({ title: {}, text: {}, url: '', _editorKey: this.createEditorKey('ideas') });
        },
        addStepItem() {
            this.stepItems.push({
                number: String(this.stepItems.length + 1).padStart(2, '0'),
                title: {},
                text: {},
                _editorKey: this.createEditorKey('steps'),
            });
        },
        addWorkItem() {
            this.workItems.push({ title: {}, text: {}, url: '', _editorKey: this.createEditorKey('works') });
        },
        moveContentItem(items, index, offset) {
            const targetIndex = index + offset;

            if (targetIndex < 0 || targetIndex >= items.length) {
                return;
            }

            const [item] = items.splice(index, 1);
            items.splice(targetIndex, 0, item);
        },
        deleteContentItem(items, index) {
            items.splice(index, 1);
        },
        changeSelectedLanguage(newSelectedLanguage) {
            this.selectedLanguage = newSelectedLanguage;
        },
        handleFormSubmit(errors) {
            this.errors = errors;
        },
        mergeSelectOptions(currentOptions, incomingOptions, selectedIds) {
            const selected = new Set((selectedIds || []).map(id => String(id)));
            const retained = (currentOptions || []).filter(option => selected.has(String(option.id)));

            return [...retained, ...(Array.isArray(incomingOptions) ? incomingOptions : [])]
                .filter((option, index, options) => options.findIndex(candidate => String(candidate.id) === String(option.id)) === index);
        },
        loadProducts(query) {
            axios.get(this.productSearchRoute + '?query=' + query).then((result) => {
                this.products = this.mergeSelectOptions(
                    this.products,
                    result.data.data,
                    this.selectedBestSalesProductsShow,
                );
            }).catch(() => {});
        },
        loadBrands(query) {
            axios.get(this.brandSearchRoute + '?query=' + query).then((result) => {
                this.brands = this.mergeSelectOptions(
                    this.brands,
                    result.data.data,
                    this.selectedBrandsShow,
                );
            }).catch(() => {});
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

                <div class="card border mb-4">
                    <div class="card-body">
                        <h4 class="mb-3">{{ $t('admin.home_page_meta') }}</h4>
                        <multi-language-input-component :title="$t('admin.meta_title')" name="meta_title" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="pageMetaTitle" :errors="errors" />
                        <multi-language-input-component :title="$t('admin.meta_description')" name="meta_description" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="pageMetaDescription" :errors="errors" />
                        <multi-language-input-component :title="$t('admin.meta_keywords')" name="meta_keywords" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="pageMetaKeywords" :errors="errors" />
                        <text-area-component :title="$t('admin.meta_tags')" name="meta_tags" :is-required="false" :init-data="productMetaTags" :errors="errors" />
                    </div>
                </div>

                <home-page-section-card-component :title="$t('admin.home_hero_section')" :help="$t('admin.home_hero_section_help')" name="content_sections[hero]" :enabled="contentSections.hero ? Boolean(contentSections.hero.enabled) : true" :initially-open="true">
                    <multi-language-input-component :title="$t('admin.home_section_kicker')" name="content_sections[hero][eyebrow]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.hero?.eyebrow || {}" :errors="errors" />
                    <div class="row">
                        <div class="col-md-7"><multi-language-input-component :title="$t('admin.home_secondary_button')" name="content_sections[hero][secondary_label]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.hero?.secondary_label || {}" :errors="errors" /></div>
                        <div class="col-md-5"><input-component :title="$t('admin.home_button_url')" name="content_sections[hero][secondary_url]" :model-value="contentSections.hero?.secondary_url || ''" :errors="errors" :is-required="false" /></div>
                    </div>
                    <h5 class="mt-3 mb-3">{{ $t('admin.slider') }}</h5>
                    <div class="row">
                        <home-page-slide-component v-for="(slide, index) in slides" :key="slide.id || `new-slide-${index}`" :slide-id="slide.hasOwnProperty('id') ? slide.id : null" :slide="slide" :index="index" :base-language="baseLanguage" :selected-language="selectedLanguage" :available-languages="availableLanguages" :errors="errors" @delete-slide="deleteSlide(index)" />
                    </div>
                    <button type="button" class="btn btn-secondary" @click="addSlide"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.slide_add') }}</button>
                </home-page-section-card-component>

                <home-page-section-card-component :title="$t('admin.home_catalog_section')" :help="$t('admin.home_catalog_section_help')" name="content_sections[catalog]" :enabled="contentSections.catalog ? Boolean(contentSections.catalog.enabled) : true">
                    <multi-language-input-component :title="$t('admin.home_section_kicker')" name="content_sections[catalog][kicker]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.catalog?.kicker || {}" :errors="errors" />
                    <multi-language-input-component :title="$t('admin.home_section_title')" name="content_sections[catalog][title]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.catalog?.title || {}" :errors="errors" />
                    <select-component :is-multi-select="true" :model-value="selectedProductTypes" :title="$t('admin.home_catalog_cards')" :options="catalogOptions" label="text" value-prop="id" name="selected_product_types" :max-items="20" :is-required="false" :errors="errors" />
                </home-page-section-card-component>

                <home-page-section-card-component :title="$t('admin.home_style_section')" :help="$t('admin.home_style_section_help')" name="style_section" :enabled="Boolean(styleSection.enabled)">
                    <multi-language-input-component :title="$t('admin.home_style_kicker')" name="style_section[kicker]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="styleSection.kicker || {}" :errors="errors" />
                    <multi-language-input-component :title="$t('admin.home_style_title')" name="style_section[title]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="styleSection.title || {}" :errors="errors" />
                    <multi-language-text-area-component :title="$t('admin.home_style_description')" name="style_section[description]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="styleSection.description || {}" :errors="errors" />
                    <div class="row">
                        <div class="col-md-7"><multi-language-input-component :title="$t('admin.home_style_cta_label')" name="style_section[cta_label]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="styleSection.cta_label || {}" :errors="errors" /></div>
                        <div class="col-md-5"><input-component :title="$t('admin.home_style_cta_url')" name="style_section[cta_url]" :model-value="styleSection.cta_url || ''" :errors="errors" :is-required="false" /></div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-3 mb-3"><strong>{{ $t('admin.home_style_items') }}</strong><button type="button" class="btn btn-sm btn-secondary" @click="addStyleItem"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.home_style_add') }}</button></div>
                    <home-page-style-item-component v-for="(item, index) in styleItems" :key="item._editorKey" :item="item" :index="index" :selected-language="selectedLanguage" :available-languages="availableLanguages" :errors="errors" :is-first="index === 0" :is-last="index === styleItems.length - 1" @delete="deleteStyleItem(index)" @move-up="moveStyleItem(index, -1)" @move-down="moveStyleItem(index, 1)" />
                </home-page-section-card-component>

                <input type="hidden" name="selected_products_id" value="">
                <home-page-section-card-component :title="$t('admin.home_popular_section')" :help="$t('admin.home_popular_section_help')" name="content_sections[popular]" :enabled="contentSections.popular ? Boolean(contentSections.popular.enabled) : true">
                    <multi-language-input-component :title="$t('admin.home_section_kicker')" name="content_sections[popular][kicker]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.popular?.kicker || {}" :errors="errors" />
                    <multi-language-input-component :title="$t('admin.home_section_title')" name="content_sections[popular][title]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.popular?.title || {}" :errors="errors" />
                    <div class="row"><div class="col-md-7"><multi-language-input-component :title="$t('admin.home_link_label')" name="content_sections[popular][link_label]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.popular?.link_label || {}" :errors="errors" /></div><div class="col-md-5"><input-component :title="$t('admin.home_link_url')" name="content_sections[popular][link_url]" :model-value="contentSections.popular?.link_url || ''" :errors="errors" :is-required="false" /></div></div>
                    <select-component :is-multi-select="true" :model-value="selectedBestSalesProductsShow" :title="$t('admin.home_popular_products')" :options="products" label="text" value-prop="id" name="selected_best_sales_products_id" :max-items="12" @search-change="loadProducts" @update:model-value="selectedBestSalesProductsShow = $event || []" :is-required="false" :errors="errors" />
                </home-page-section-card-component>

                <home-page-section-card-component :title="$t('admin.home_numbers_section')" :help="$t('admin.home_numbers_section_help')" name="content_sections[numbers]" :enabled="contentSections.numbers ? Boolean(contentSections.numbers.enabled) : true">
                    <multi-language-input-component :title="$t('admin.home_section_kicker')" name="content_sections[numbers][kicker]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.numbers?.kicker || {}" :errors="errors" />
                    <multi-language-input-component :title="$t('admin.home_section_title')" name="content_sections[numbers][title]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.numbers?.title || {}" :errors="errors" />
                    <div class="d-flex align-items-center justify-content-between mt-3 mb-3"><strong>{{ $t('admin.home_metrics') }}</strong><button type="button" class="btn btn-sm btn-secondary" @click="addNumberItem"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.home_add_item') }}</button></div>
                    <home-page-number-item-component v-for="(item, index) in numberItems" :key="item._editorKey" :item="item" :index="index" :selected-language="selectedLanguage" :available-languages="availableLanguages" :errors="errors" :is-first="index === 0" :is-last="index === numberItems.length - 1" @delete="deleteContentItem(numberItems, index)" @move-up="moveContentItem(numberItems, index, -1)" @move-down="moveContentItem(numberItems, index, 1)" />
                </home-page-section-card-component>

                <home-page-section-card-component :title="$t('admin.home_ideas_section')" :help="$t('admin.home_ideas_section_help')" name="content_sections[ideas]" :enabled="contentSections.ideas ? Boolean(contentSections.ideas.enabled) : true">
                    <multi-language-input-component :title="$t('admin.home_section_kicker')" name="content_sections[ideas][kicker]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.ideas?.kicker || {}" :errors="errors" />
                    <multi-language-input-component :title="$t('admin.home_section_title')" name="content_sections[ideas][title]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.ideas?.title || {}" :errors="errors" />
                    <div class="d-flex align-items-center justify-content-between mt-3 mb-3"><strong>{{ $t('admin.home_idea_items') }}</strong><button type="button" class="btn btn-sm btn-secondary" @click="addIdeaItem"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.home_add_item') }}</button></div>
                    <home-page-visual-item-component v-for="(item, index) in ideaItems" :key="item._editorKey" section="ideas" :show-url="true" :item="item" :index="index" :selected-language="selectedLanguage" :available-languages="availableLanguages" :errors="errors" :is-first="index === 0" :is-last="index === ideaItems.length - 1" @delete="deleteContentItem(ideaItems, index)" @move-up="moveContentItem(ideaItems, index, -1)" @move-down="moveContentItem(ideaItems, index, 1)" />
                </home-page-section-card-component>

                <home-page-section-card-component :title="$t('admin.home_steps_section')" :help="$t('admin.home_steps_section_help')" name="content_sections[steps]" :enabled="contentSections.steps ? Boolean(contentSections.steps.enabled) : true">
                    <multi-language-input-component :title="$t('admin.home_section_kicker')" name="content_sections[steps][kicker]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.steps?.kicker || {}" :errors="errors" />
                    <multi-language-input-component :title="$t('admin.home_section_title')" name="content_sections[steps][title]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.steps?.title || {}" :errors="errors" />
                    <div class="row"><div class="col-md-7"><multi-language-input-component :title="$t('admin.home_button_label')" name="content_sections[steps][cta_label]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.steps?.cta_label || {}" :errors="errors" /></div><div class="col-md-5"><input-component :title="$t('admin.home_button_url')" name="content_sections[steps][cta_url]" :model-value="contentSections.steps?.cta_url || ''" :errors="errors" :is-required="false" /></div></div>
                    <div class="d-flex align-items-center justify-content-between mt-3 mb-3"><strong>{{ $t('admin.home_step_items') }}</strong><button type="button" class="btn btn-sm btn-secondary" @click="addStepItem"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.home_add_item') }}</button></div>
                    <home-page-step-item-component v-for="(item, index) in stepItems" :key="item._editorKey" :item="item" :index="index" :selected-language="selectedLanguage" :available-languages="availableLanguages" :errors="errors" :is-first="index === 0" :is-last="index === stepItems.length - 1" @delete="deleteContentItem(stepItems, index)" @move-up="moveContentItem(stepItems, index, -1)" @move-down="moveContentItem(stepItems, index, 1)" />
                </home-page-section-card-component>

                <home-page-section-card-component :title="$t('admin.home_works_section')" :help="$t('admin.home_works_section_help')" name="content_sections[works]" :enabled="contentSections.works ? Boolean(contentSections.works.enabled) : true">
                    <multi-language-input-component :title="$t('admin.home_section_kicker')" name="content_sections[works][kicker]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.works?.kicker || {}" :errors="errors" />
                    <multi-language-input-component :title="$t('admin.home_section_title')" name="content_sections[works][title]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.works?.title || {}" :errors="errors" />
                    <div class="row"><div class="col-md-7"><multi-language-input-component :title="$t('admin.home_link_label')" name="content_sections[works][link_label]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.works?.link_label || {}" :errors="errors" /></div><div class="col-md-5"><input-component :title="$t('admin.home_link_url')" name="content_sections[works][link_url]" :model-value="contentSections.works?.link_url || ''" :errors="errors" :is-required="false" /></div></div>
                    <div class="d-flex align-items-center justify-content-between mt-3 mb-3"><strong>{{ $t('admin.home_work_items') }}</strong><button type="button" class="btn btn-sm btn-secondary" @click="addWorkItem"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.home_add_item') }}</button></div>
                    <home-page-visual-item-component v-for="(item, index) in workItems" :key="item._editorKey" section="works" :show-url="true" :item="item" :index="index" :selected-language="selectedLanguage" :available-languages="availableLanguages" :errors="errors" :is-first="index === 0" :is-last="index === workItems.length - 1" @delete="deleteContentItem(workItems, index)" @move-up="moveContentItem(workItems, index, -1)" @move-down="moveContentItem(workItems, index, 1)" />
                </home-page-section-card-component>

                <home-page-section-card-component :title="$t('admin.home_reviews_section')" :help="$t('admin.home_reviews_section_help')" name="content_sections[reviews]" :enabled="contentSections.reviews ? Boolean(contentSections.reviews.enabled) : true">
                    <multi-language-input-component :title="$t('admin.home_section_kicker')" name="content_sections[reviews][kicker]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.reviews?.kicker || {}" :errors="errors" />
                    <multi-language-input-component :title="$t('admin.home_section_title')" name="content_sections[reviews][title]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.reviews?.title || {}" :errors="errors" />
                    <div class="row"><div class="col-md-7"><multi-language-input-component :title="$t('admin.home_link_label')" name="content_sections[reviews][link_label]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.reviews?.link_label || {}" :errors="errors" /></div><div class="col-md-5"><input-component :title="$t('admin.home_link_url')" name="content_sections[reviews][link_url]" :model-value="contentSections.reviews?.link_url || ''" :errors="errors" :is-required="false" /></div></div>
                    <div class="form-group mb-3 art-admin-repeater-four-width"><home-page-testimonial-component v-for="(testimonial, index) in testimonials" :key="testimonial.id || `testimonial-${index}`" :testimonial-id="testimonial.hasOwnProperty('id') ? testimonial.id : null" :testimonial="testimonial" :index="index" :base-language="baseLanguage" :selected-language="selectedLanguage" :available-languages="availableLanguages" :rating-options="testimonialsRatingOptions" :errors="errors" @delete-testimonial="deleteTestimonial(index)" /></div>
                    <button type="button" class="btn btn-secondary" @click="addTestimonial"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.testimonial_add') }}</button>
                </home-page-section-card-component>

                <home-page-section-card-component :title="$t('admin.home_instagram_section')" :help="$t('admin.home_instagram_section_help')" name="content_sections[instagram]" :enabled="contentSections.instagram ? Boolean(contentSections.instagram.enabled) : true">
                    <multi-language-input-component :title="$t('admin.home_section_kicker')" name="content_sections[instagram][kicker]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.instagram?.kicker || {}" :errors="errors" />
                    <multi-language-input-component :title="$t('admin.home_section_title')" name="content_sections[instagram][title]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.instagram?.title || {}" :errors="errors" />
                    <div class="row"><div class="col-md-7"><multi-language-input-component :title="$t('admin.home_button_label')" name="content_sections[instagram][link_label]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.instagram?.link_label || {}" :errors="errors" /></div><div class="col-md-5"><input-component :title="$t('admin.home_link_url')" name="content_sections[instagram][link_url]" :model-value="contentSections.instagram?.link_url || ''" :errors="errors" :is-required="false" /></div></div>
                    <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between mb-0" style="gap: 12px">
                        <span>{{ $t('admin.home_instagram_source_help') }}</span>
                        <a v-if="instagramAuthRoute" :href="instagramAuthRoute" class="btn btn-primary btn-sm">{{ $t('admin.home_instagram_connect') }}</a>
                    </div>
                </home-page-section-card-component>

                <home-page-section-card-component :title="$t('admin.home_blog_section')" :help="$t('admin.home_blog_section_help')" name="content_sections[blog]" :enabled="contentSections.blog ? Boolean(contentSections.blog.enabled) : true">
                    <multi-language-input-component :title="$t('admin.home_section_kicker')" name="content_sections[blog][kicker]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.blog?.kicker || {}" :errors="errors" />
                    <multi-language-input-component :title="$t('admin.home_section_title')" name="content_sections[blog][title]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.blog?.title || {}" :errors="errors" />
                    <div class="row"><div class="col-md-7"><multi-language-input-component :title="$t('admin.home_link_label')" name="content_sections[blog][link_label]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.blog?.link_label || {}" :errors="errors" /></div><div class="col-md-5"><input-component :title="$t('admin.home_link_url')" name="content_sections[blog][link_url]" :model-value="contentSections.blog?.link_url || ''" :errors="errors" :is-required="false" /></div></div>
                    <p class="text-muted mb-0">{{ $t('admin.home_blog_source_help') }}</p>
                </home-page-section-card-component>

                <home-page-section-card-component :title="$t('admin.home_faq_section')" :help="$t('admin.home_faq_section_help')" name="content_sections[faq]" :enabled="contentSections.faq ? Boolean(contentSections.faq.enabled) : true">
                    <multi-language-input-component :title="$t('admin.home_section_kicker')" name="content_sections[faq][kicker]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.faq?.kicker || {}" :errors="errors" />
                    <multi-language-input-component :title="$t('admin.home_section_title')" name="content_sections[faq][title]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.faq?.title || {}" :errors="errors" />
                    <div class="form-group mb-3 art-admin-repeater-four-width"><home-page-faq-component v-for="(faq, index) in faqs" :key="faq.id || `faq-${index}`" :faq-id="faq.hasOwnProperty('id') ? faq.id : null" :faq="faq" :index="index" :base-language="baseLanguage" :selected-language="selectedLanguage" :available-languages="availableLanguages" :errors="errors" :faq-deleted="faqDeleted" @delete-faq="deleteFaq(index)" /></div>
                    <button type="button" class="btn btn-secondary" @click="addFaq"><span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.question_add') }}</button>
                </home-page-section-card-component>

                <home-page-section-card-component :title="$t('admin.home_partners_section')" :help="$t('admin.home_partners_section_help')" name="content_sections[partners]" :enabled="contentSections.partners ? Boolean(contentSections.partners.enabled) : true">
                    <multi-language-input-component :title="$t('admin.home_section_kicker')" name="content_sections[partners][kicker]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.partners?.kicker || {}" :errors="errors" />
                    <multi-language-input-component :title="$t('admin.home_section_title')" name="content_sections[partners][title]" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="contentSections.partners?.title || {}" :errors="errors" />
                    <select-component :is-multi-select="true" :model-value="selectedBrandsShow" :title="$t('admin.brands')" :options="brands" label="text" value-prop="id" name="selected_brands_id" :max-items="12" @search-change="loadBrands" @update:model-value="selectedBrandsShow = $event || []" :is-required="false" :errors="errors" />
                </home-page-section-card-component>

                <home-page-section-card-component :title="$t('admin.home_seo_section')" :help="$t('admin.home_seo_section_help')" name="content_sections[seo]" :enabled="contentSections.seo ? Boolean(contentSections.seo.enabled) : true">
                    <multi-language-input-component :title="$t('admin.seo_title')" name="seo_title" :selected-language="selectedLanguage" :available-languages="availableLanguages" :is-required="false" :init-data="seoTitle" :errors="errors" />
                    <multi-language-rich-text-editor-component :title="$t('admin.seo_text')" name="seo_text" :selected-language="selectedLanguage" :available-languages="availableLanguages" :content="seoText" :errors="errors" />
                </home-page-section-card-component>

            </div>
        </div>
    </reactive-form-container>
</template>
