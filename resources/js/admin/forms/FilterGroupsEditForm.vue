<script>
import slug from "slug";

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
        brands: {
            type: Array,
            default: [],
        },
        countries: {
            type: Array,
            default: [],
        },
        colors: {
            type: Array,
            default: [],
        },
        success: {
            type: String,
            default: null,
        },
        error: {
            type: String,
            default: null,
        },

        //data
        name: {
            type: Object,
            default: {},
        },
        slug: {
            type: String,
            default: '',
        },
        titleTag: {
            type: Object,
            default: {},
        },
        metaTitle: {
            type: Object,
            default: {},
        },
        metaDescription: {
            type: Object,
            default: {},
        },
        metaKeywords: {
            type: Object,
            default: {},
        },
        productTypeId: {
            type: Number,
            default: null,
        },
        filters: {
            type: Object,
            default: {},
        }
    },
    data() {
        return {
            selectedLanguage: this.baseLanguage,
            selectedProductTypeId: null,
            selectedBrandId: null,
            slugData: '',
            groupNameData: {},
            errors: [],
        }
    },
    beforeMount() {
        this.selectedProductTypeId = this.productTypeId;
        this.groupNameData = this.name;
        this.slugData = this.slug;
    },
    computed: {
        groupNameOnBaseLanguage() {
            return this.groupNameData[this.baseLanguage];
        },
        selectedProductTypeData() {
            if (this.selectedProductTypeId !== null) {
                return this.productTypes.find(productType => productType.id === this.selectedProductTypeId);
            }

            return null;
        },
    },
    watch: {
        groupNameOnBaseLanguage(newArticleName, oldArticleName) {
            this.slugData = slug(newArticleName, {locale: this.baseLanguage});
        }
    },
    methods: {
        changeSelectedLanguage(newSelectedLanguage) {
            this.selectedLanguage = newSelectedLanguage;
        },
        handleFormSubmit(errors) {
            this.errors = errors;
        },
        getCustomFieldValue(fieldId)
        {
            if (this.filters.hasOwnProperty('custom_fields')) {
                const value = this.filters.custom_fields.find(customField => customField.id === fieldId);
                if (value) {
                    return value.value;
                }
            }

            return null;
        }
    }

}
</script>

<template>
    <reactive-form-container
        :submit-route="submitRoute"
        @on-selected-language-change="changeSelectedLanguage"
        @on-errors-change="handleFormSubmit"
        :card-body-title="$t('admin.filter_groups') "
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
                <multi-language-input-component
                    name="name"
                    :is-required="true"
                    :title="$t('admin.name')  + ' ' + $t('admin.h1_tag') + ' '"
                    :available-languages="availableLanguages"
                    :selected-language="selectedLanguage"
                    :init-data="groupNameData"
                    v-model="groupNameData"
                    :errors="errors"
                />
                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.slug')"
                        name="slug"
                        :is-required="true"
                        :errors="errors"
                        v-model="slugData"
                    />
                </div>
                <multi-language-input-component
                    name="title_tag"
                    :is-required="true"
                    :title="$t('admin.title_tag')"
                    :available-languages="availableLanguages"
                    :selected-language="selectedLanguage"
                    :init-data="titleTag"
                    :errors="errors"
                />
                <multi-language-input-component
                    name="meta_title"
                    :is-required="true"
                    :title="$t('admin.meta_title')"
                    :available-languages="availableLanguages"
                    :selected-language="selectedLanguage"
                    :init-data="metaTitle"
                    :errors="errors"
                />
                <multi-language-input-component
                    name="meta_description"
                    :is-required="true"
                    :title="$t('admin.meta_description')"
                    :available-languages="availableLanguages"
                    :selected-language="selectedLanguage"
                    :init-data="metaDescription"
                    :errors="errors"
                />
                <multi-language-input-component
                    name="meta_keywords"
                    :is-required="true"
                    :title="$t('admin.meta_keywords')"
                    :available-languages="availableLanguages"
                    :selected-language="selectedLanguage"
                    :init-data="metaKeywords"
                    :errors="errors"
                />
                <select-component
                    :title="$t('admin.product_type')"
                    :options="productTypes"
                    label="name"
                    value-prop="id"
                    name="product_type_id"
                    :is-required="true"
                    v-model="selectedProductTypeId"
                    :errors="errors"
                />

                <!-- custom fields start -->

                <div class="row mt-5" v-if="selectedProductTypeData !== null">
                    <div class="col">
                        <div class="row">
                            <div class="col">
                                <p>
                                    <strong>
                                        {{ $t('admin.filters') }}
                                    </strong>
                                </p>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <input-component
                                :title="$t('admin.price_from')"
                                type="number"
                                name="price_from"
                                :model-value="filters.hasOwnProperty('price_from') ? filters.price_from : null"
                                :errors="errors"
                            />
                        </div>
                        <div class="form-group mb-3">
                            <input-component
                                :title="$t('admin.price_to')"
                                type="number"
                                name="price_to"
                                :model-value="filters.hasOwnProperty('price_to') ? filters.price_to : null"
                                :errors="errors"
                            />
                        </div>
                        <div class="form-group mb-3" v-if="selectedProductTypeData.hasOwnProperty('has_color') && selectedProductTypeData.has_color">
                            <select-component
                                :title="$t('admin.colors')"
                                :is-multi-select="true"
                                :options="colors"
                                label="name"
                                value-prop="id"
                                name="color_ids"
                                :model-value="filters.hasOwnProperty('color_ids') ? filters.color_ids : null"
                                :errors="errors"
                            />
                        </div>
                        <div class="form-group mb-3" v-if="selectedProductTypeData.hasOwnProperty('has_brand') && selectedProductTypeData.has_brand">
                            <select-component
                                :title="$t('admin.brands')"
                                :is-multi-select="true"
                                :options="brands"
                                label="name"
                                value-prop="id"
                                name="brand_ids"
                                :model-value="filters.hasOwnProperty('brand_ids') ? filters.color_ids : null"
                                :errors="errors"
                            />
                        </div>
                        <div class="form-group mb-3">
                            <select-component
                                :title="$t('admin.countries')"
                                :is-multi-select="true"
                                :options="countries"
                                label="name"
                                value-prop="id"
                                name="country_ids"
                                :model-value="filters.hasOwnProperty('country_ids') ? filters.country_ids : null"
                                :errors="errors"
                            />
                        </div>

                        <!-- sizes start -->

                        <div class="form-group mb-3" v-if="selectedProductTypeData.hasOwnProperty('has_length') &&
                            selectedProductTypeData.has_length &&
                            selectedProductTypeData.hasOwnProperty('length_options') && Array.isArray(selectedProductTypeData.length_options) &&
                            selectedProductTypeData.length_options.length <= 0
                        ">
                            <input-component
                                :title="$t('admin.length_from')"
                                type="number"
                                name="length_from"
                                :model-value="filters.hasOwnProperty('length_from') ? filters.length_from : null"
                                :errors="errors"
                            />
                            <input-component
                                :title="$t('admin.length_to')"
                                type="number"
                                name="length_to"
                                :model-value="filters.hasOwnProperty('length_to') ? filters.length_to : null"
                                :errors="errors"
                            />
                        </div>

                        <select-component
                            v-if="selectedProductTypeData.hasOwnProperty('has_length') &&
                            selectedProductTypeData.has_length &&
                            selectedProductTypeData.hasOwnProperty('length_options') && Array.isArray(selectedProductTypeData.length_options) &&
                            selectedProductTypeData.length_options.length > 0
                            "
                            :title="$t('admin.length_options')"
                            :is-multi-select="true"
                            :options="selectedProductTypeData.length_options"
                            label="name"
                            value-prop="id"
                            name="length_options"
                            :model-value="filters.hasOwnProperty('length_options') ? filters.length_options : null"
                            :errors="errors"
                        />

                        <div class="form-group mb-3" v-if="selectedProductTypeData.hasOwnProperty('has_width') &&
                            selectedProductTypeData.has_width &&
                            selectedProductTypeData.hasOwnProperty('width_options') && Array.isArray(selectedProductTypeData.width_options) &&
                            selectedProductTypeData.width_options.length <= 0
                        ">
                            <input-component
                                :title="$t('admin.width_from')"
                                type="number"
                                name="width_from"
                                :model-value="filters.hasOwnProperty('width_from') ? filters.width_from : null"
                                :errors="errors"
                            />
                            <input-component
                                :title="$t('admin.width_to')"
                                type="number"
                                name="width_to"
                                :model-value="filters.hasOwnProperty('width_to') ? filters.width_to : null"
                                :errors="errors"
                            />
                        </div>

                        <select-component
                            v-if="selectedProductTypeData.hasOwnProperty('has_width') &&
                            selectedProductTypeData.has_width &&
                            selectedProductTypeData.hasOwnProperty('width_options') && Array.isArray(selectedProductTypeData.width_options) &&
                            selectedProductTypeData.width_options.length > 0
                            "
                            :title="$t('admin.width_options')"
                            :is-multi-select="true"
                            :options="selectedProductTypeData.width_options"
                            label="name"
                            value-prop="id"
                            name="width_options"
                            :model-value="filters.hasOwnProperty('width_options') ? filters.width_options : null"
                            :errors="errors"
                        />

                        <div class="form-group mb-3" v-if="selectedProductTypeData.hasOwnProperty('has_height') &&
                            selectedProductTypeData.has_height &&
                            selectedProductTypeData.hasOwnProperty('height_options') && Array.isArray(selectedProductTypeData.height_options) &&
                            selectedProductTypeData.height_options.length <= 0
                        ">
                            <input-component
                                :title="$t('admin.height_from')"
                                type="number"
                                name="height_from"
                                :model-value="filters.hasOwnProperty('height_from') ? filters.height_from : null"
                                :errors="errors"
                            />
                            <input-component
                                :title="$t('admin.height_to')"
                                type="number"
                                name="height_to"
                                :model-value="filters.hasOwnProperty('height_to') ? filters.height_to : null"
                                :errors="errors"
                            />
                        </div>

                        <select-component
                            v-if="selectedProductTypeData.hasOwnProperty('has_height') &&
                            selectedProductTypeData.has_height &&
                            selectedProductTypeData.hasOwnProperty('height_options') && Array.isArray(selectedProductTypeData.height_options) &&
                            selectedProductTypeData.height_options.length > 0
                            "
                            :title="$t('admin.height_options')"
                            :is-multi-select="true"
                            :options="selectedProductTypeData.height_options"
                            label="name"
                            value-prop="id"
                            name="height_options"
                            :model-value="filters.hasOwnProperty('height_options') ? filters.height_options : null"
                            :errors="errors"
                        />

                        <div class="row" v-if="selectedProductTypeData.hasOwnProperty('custom_fields')" v-for="(customField, index) in selectedProductTypeData.custom_fields">
                            <div class="col">
                                <div class="row" v-if="customField.hasOwnProperty('field_type_id') && customField.field_type_id === 1">
                                    <div class="col">
                                        <div class="form-group mb-3">
                                            <input type="hidden" :name="'custom_field[' + index + '][id]'" :value="customField.id"/>
                                            <input-component
                                                :title="customField.name"
                                                type="text"
                                                :name="'custom_field[' + index + '][value]'"
                                                :model-value="getCustomFieldValue(customField.id)"
                                                :errors="errors"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="row" v-if="customField.hasOwnProperty('field_type_id') && customField.field_type_id === 2">
                                    <div class="col">
                                        <div class="form-group mb-3">
                                            <input type="hidden" :name="'custom_field[' + index + '][id]'" :value="customField.id"/>
                                            <input-component
                                                :title="customField.name"
                                                type="number"
                                                :name="'custom_field[' + index + '][value]'"
                                                :model-value="getCustomFieldValue(customField.id)"
                                                :errors="errors"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="row" v-if="customField.hasOwnProperty('field_type_id') && customField.field_type_id === 3">
                                    <div class="col">
                                        <div class="form-group mb-3">
                                            <input type="hidden" :name="'custom_field[' + index + '][id]'" :value="customField.id"/>
                                            <input-component
                                                :title="customField.name"
                                                type="number"
                                                :name="'custom_field[' + index + '][value]'"
                                                :model-value="getCustomFieldValue(customField.id)"
                                                :errors="errors"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="row" v-if="customField.hasOwnProperty('field_type_id') && customField.field_type_id === 4">
                                    <div class="col">
                                        <div class="form-group mb-3">
                                            <input type="hidden" :name="'custom_field[' + index + '][id]'" :value="customField.id"/>
                                            <select-component
                                                :title="customField.name"
                                                :is-multi-select="true"
                                                :options="customField.hasOwnProperty('options') ? customField.options : []"
                                                label="name"
                                                value-prop="id"
                                                :name="'custom_field[' + index + '][value]'"
                                                :model-value="getCustomFieldValue(customField.id)"
                                                :errors="errors"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- sizes end -->
                    </div>
                </div>
                <!-- custom fields end -->
            </div>
        </div>
    </reactive-form-container>
</template>
