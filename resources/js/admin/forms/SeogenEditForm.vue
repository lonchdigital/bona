<script>
const TAB_CATEGORIES = 'CATEGORIES';
const TAB_PRODUCTS = 'PRODUCTS';
const TAB_BRANDS = 'BRANDS';
export default {
    props: {
        submitRoute: {
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
        productTypes: {
            type: Array,
            default: [],
        },
        seogenCategories: {
            type: Array,
            default: [],
        },
        seogenProducts: {
            type: Array,
            default: [],
        },
        seogenBrand: {
            type: Object,
            default: [],
        },
        success: {
            type: String,
            default: null,
        },
        error: {
            type: String,
            default: null,
        }
    },
    data() {
        return {
            selectedTab: TAB_CATEGORIES,
            selectedLanguage: this.baseLanguage,
            selectedCategoryProductTypeTab: 1,
            selectedProductProductTypeTab: 1,
            fieldsData: {
                CATEGORIES: {
                    title_tag: {},
                    h1_tag: {},
                },
                PRODUCTS: {
                    title_tag: {},
                    h1_tag: {},
                }
            },
            errors: [],
        }
    },
    mounted() {
    },
    computed: {
        categoriesTabHasErrors() {
            for (const [key, value] of Object.entries(this.errors)) {
                if (key.includes('product_category')) {
                    return true;
                }
            }

            return false;
        },
        productsTabHasErrors() {
            for (const [key, value] of Object.entries(this.errors)) {
                if (key.includes('product.')) {
                    return true;
                }
            }

            return false;
        },
        brandTabHasErrors() {
            for (const [key, value] of Object.entries(this.errors)) {
                if (key.includes('brand')) {
                    return true;
                }
            }

            return false;
        }
    },
    methods: {
        selectCategoriesTab() {
            this.selectedTab = TAB_CATEGORIES;
        },
        selectProductsTab() {
            this.selectedTab = TAB_PRODUCTS;
        },
        selectBrandTab() {
            this.selectedTab = TAB_BRANDS;
        },
        selectCategoriesProductTypeTab(productTypeId) {
            this.selectedCategoryProductTypeTab = productTypeId;
        },
        selectProductsProductTypeTab(productTypeId) {
            this.selectedProductProductTypeTab = productTypeId;
        },
        changeSelectedLanguage(newSelectedLanguage) {
            this.selectedLanguage = newSelectedLanguage;
        },
        handleFormSubmit(errors) {
            this.errors = errors;
        },
        categoriesProductTypeHasError(productTypeId) {
            for (const [key, value] of Object.entries(this.errors)) {
                if (key.includes('product_category.' + productTypeId)) {
                    return true;
                }
            }

            return false;
        },
        getSeogenCategoriesFieldsByProductTypeId(productTypeId, fieldName) {
            const value = this.seogenCategories.find(function (seogenCategory) {
                return seogenCategory.hasOwnProperty('product_type_id') && seogenCategory.product_type_id === productTypeId &&
                    seogenCategory.hasOwnProperty('page_type') && seogenCategory.page_type === 'PRODUCT_CATEGORY';
            });

            if (value.hasOwnProperty(fieldName)) {
                return value[fieldName];
            }

            return [];
        },
        getSeogenProductFieldsByProductTypeId(productTypeId, fieldName) {
            const value = this.seogenProducts.find(function (seogenCategory) {
                return seogenCategory.hasOwnProperty('product_type_id') && seogenCategory.product_type_id === productTypeId &&
                    seogenCategory.hasOwnProperty('page_type') && seogenCategory.page_type === 'PRODUCT';
            });

            if (value.hasOwnProperty(fieldName)) {
                return value[fieldName];
            }

            return [];
        }
    }
}
</script>

<template>
    <reactive-form-container
        :submit-route="submitRoute"
        @on-selected-language-change="changeSelectedLanguage"
        @on-errors-change="handleFormSubmit"
        :card-body-title="$t('admin.seogen_config') "
    >
    <div class="row" v-if="success">
        <div class="col-md-12">
            <div class="alert alert-success" role="alert">
                {{ success }}
            </div>
        </div>
    </div>
    <div class="row" v-if="error">
        <div class="col-md-12">
            <div class="alert alert-danger" role="alert">
                {{ error }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="row">
                <div class="col">
                    <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" :class="{'text-danger': categoriesTabHasErrors}" id="home-tab" data-toggle="tab" role="tab" aria-controls="categories" @click="selectCategoriesTab()">{{ $t('admin.product_categories') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{'text-danger': productsTabHasErrors}" id="profile-tab" data-toggle="tab" role="tab" aria-controls="products" @click="selectProductsTab()">{{ $t('admin.products') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{'text-danger': brandTabHasErrors}" id="contact-tab" data-toggle="tab" role="tab" aria-controls="brands" @click="selectBrandTab()">{{ $t('admin.brands') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row" :class="{'d-none': selectedTab !== 'CATEGORIES'}" v-if="productTypes.length > 0">
                <div class="col">
                    <div class="row">
                        <div class="col">
                            <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                                <li class="nav-item" v-for="(productType, index) in productTypes">
                                    <a class="nav-link" :class="{'active': selectedCategoryProductTypeTab === productType.id, 'text-danger': categoriesProductTypeHasError(index)}" id="home-tab" data-toggle="tab" role="tab" aria-controls="product_types" @click.prevent="selectCategoriesProductTypeTab(productType.id)" >{{ productType.name }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="row" v-for="(productType, index) in productTypes" :class="{'d-none': productType.id !== selectedCategoryProductTypeTab}" :key="'categories-' + productType.id + '-' + index">
                        <div class="col">
                            <input type="hidden"  :name="'product_category[' + index + '][product_type_id]'" :value="productType.id">
                            <multi-language-input-component
                                :name="'product_category[' + index + '][title_tag]'"
                                :is-required="true"
                                :title="$t('admin.title_tag')  + ' ' + $t('admin.category_available_tags')"
                                :available-languages="availableLanguages"
                                :selected-language="selectedLanguage"
                                :init-data="getSeogenCategoriesFieldsByProductTypeId(productType.id, 'html_title_tag')"
                                :key="'categories-title_tag-' + productType.id + '-' + index"
                                v-model="fieldsData.CATEGORIES.title_tag"
                                :errors="errors"
                            />
                            <multi-language-input-component
                                :name="'product_category[' + index + '][h1_tag]'"
                                :is-required="true"
                                :title="$t('admin.h1_tag')  + ' ' + $t('admin.category_available_tags')"
                                :available-languages="availableLanguages"
                                :selected-language="selectedLanguage"
                                :init-data="getSeogenCategoriesFieldsByProductTypeId(productType.id, 'html_h1_tag')"
                                :key="'categories-h1_tag-' + productType.id + '-' + index"
                                v-model="fieldsData.CATEGORIES.h1_tag"
                                :errors="errors"
                            />
                            <multi-language-input-component
                                :name="'product_category[' + index + '][meta_title_tag]'"
                                :is-required="true"
                                :title="$t('admin.meta_title_tag') + ' ' + $t('admin.category_available_tags')"
                                :available-languages="availableLanguages"
                                :selected-language="selectedLanguage"
                                :init-data="getSeogenCategoriesFieldsByProductTypeId(productType.id, 'meta_title_tag')"
                                :errors="errors"
                            />
                            <multi-language-input-component
                                :name="'product_category[' + index + '][meta_description_tag]'"
                                :is-required="true"
                                :title="$t('admin.meta_description_tag') + ' ' + $t('admin.category_available_tags')"
                                :available-languages="availableLanguages"
                                :selected-language="selectedLanguage"
                                :init-data="getSeogenCategoriesFieldsByProductTypeId(productType.id, 'meta_description_tag')"
                                :errors="errors"
                            />
                            <multi-language-input-component
                                :name="'product_category[' + index + '][meta_keywords_tag]'"
                                :is-required="true"
                                :title="$t('admin.meta_keywords_tag') + ' ' + $t('admin.category_available_tags')"
                                :available-languages="availableLanguages"
                                :selected-language="selectedLanguage"
                                :init-data="getSeogenCategoriesFieldsByProductTypeId(productType.id, 'meta_keywords_tag')"
                                :errors="errors"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" :class="{'d-none': selectedTab !== 'PRODUCTS'}">
                <div class="col">
                    <div class="row">
                        <div class="col">
                            <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                                <li class="nav-item" v-for="(productType, index) in productTypes">
                                    <a class="nav-link" :class="{'active': index === 0}" id="home-tab" data-toggle="tab" role="tab" aria-controls="product_types" @click.prevent="selectProductsProductTypeTab(productType.id)" >{{ productType.name }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="row" v-for="(productType, index) in productTypes" :class="{'d-none': productType.id !== selectedProductProductTypeTab}">
                        <div class="col">
                            <input type="hidden"  :name="'product[' + index + '][product_type_id]'" :value="productType.id">
                            <multi-language-input-component
                                :name="'product[' + index + '][title_tag]'"
                                :is-required="true"
                                :title="$t('admin.title_tag')  + ' ' + $t('admin.product_available_tags')"
                                :available-languages="availableLanguages"
                                :selected-language="selectedLanguage"
                                :init-data="getSeogenProductFieldsByProductTypeId(productType.id, 'html_title_tag')"
                                :key="'products-title_tag-' + productType.id + '-' + index"
                                v-model="fieldsData.PRODUCTS.title_tag"
                                :errors="errors"
                            />
                            <multi-language-input-component
                                :name="'product[' + index + '][h1_tag]'"
                                :is-required="true"
                                :title="$t('admin.h1_tag')  + ' ' + $t('admin.product_available_tags')"
                                :available-languages="availableLanguages"
                                :selected-language="selectedLanguage"
                                :init-data="getSeogenProductFieldsByProductTypeId(productType.id, 'html_h1_tag')"
                                :errors="errors"
                            />
                            <multi-language-input-component
                                :name="'product[' + index + '][meta_title_tag]'"
                                :is-required="true"
                                :title="$t('admin.meta_title_tag') + ' ' + $t('admin.product_available_tags')"
                                :available-languages="availableLanguages"
                                :selected-language="selectedLanguage"
                                :init-data="getSeogenProductFieldsByProductTypeId(productType.id, 'meta_title_tag')"
                                :errors="errors"
                            />
                            <multi-language-input-component
                                :name="'product[' + index + '][meta_description_tag]'"
                                :is-required="true"
                                :title="$t('admin.meta_description_tag') + ' ' + $t('admin.product_available_tags')"
                                :available-languages="availableLanguages"
                                :selected-language="selectedLanguage"
                                :init-data="getSeogenProductFieldsByProductTypeId(productType.id, 'meta_description_tag')"
                                :errors="errors"
                            />
                            <multi-language-input-component
                                :name="'product[' + index + '][meta_keywords_tag]'"
                                :is-required="true"
                                :title="$t('admin.meta_keywords_tag') + ' ' + $t('admin.product_available_tags')"
                                :available-languages="availableLanguages"
                                :selected-language="selectedLanguage"
                                :init-data="getSeogenProductFieldsByProductTypeId(productType.id, 'meta_keywords_tag')"
                                :errors="errors"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" :class="{'d-none': selectedTab !== 'BRANDS'}">
                <div class="col">
                    <multi-language-input-component
                        :name="'brand_title_tag'"
                        :is-required="true"
                        :title="$t('admin.title_tag')  + ' ' + $t('admin.brand_available_tags')"
                        :available-languages="availableLanguages"
                        :selected-language="selectedLanguage"
                        :init-data="seogenBrand.hasOwnProperty('html_title_tag') ? seogenBrand.html_title_tag : []"
                        :errors="errors"
                    />
                    <multi-language-input-component
                        :name="'brand_h1_tag'"
                        :is-required="true"
                        :title="$t('admin.h1_tag')  + ' ' + $t('admin.brand_available_tags')"
                        :available-languages="availableLanguages"
                        :selected-language="selectedLanguage"
                        :init-data="seogenBrand.hasOwnProperty('html_h1_tag') ? seogenBrand.html_h1_tag : []"
                        :errors="errors"
                    />
                    <multi-language-input-component
                        :name="'brand_meta_title_tag'"
                        :is-required="true"
                        :title="$t('admin.meta_title_tag') + ' ' + $t('admin.brand_available_tags')"
                        :available-languages="availableLanguages"
                        :selected-language="selectedLanguage"
                        :init-data="seogenBrand.hasOwnProperty('meta_title_tag') ? seogenBrand.meta_title_tag : []"
                        :errors="errors"
                    />
                    <multi-language-input-component
                        :name="'brand_meta_description_tag'"
                        :is-required="true"
                        :title="$t('admin.meta_description_tag') + ' ' + $t('admin.brand_available_tags')"
                        :available-languages="availableLanguages"
                        :selected-language="selectedLanguage"
                        :init-data="seogenBrand.hasOwnProperty('meta_description_tag') ? seogenBrand.meta_description_tag : []"
                        :errors="errors"
                    />
                    <multi-language-input-component
                        :name="'brand_meta_keywords_tag'"
                        :is-required="true"
                        :title="$t('admin.meta_keywords_tag') + ' ' + $t('admin.brand_available_tags')"
                        :available-languages="availableLanguages"
                        :selected-language="selectedLanguage"
                        :init-data="seogenBrand.hasOwnProperty('meta_keywords_tag') ? seogenBrand.meta_keywords_tag : []"
                        :errors="errors"
                    />
                </div>
            </div>
        </div>
    </div>
    </reactive-form-container>
</template>
