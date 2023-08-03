<script>
import axios from "axios";
import slug from "slug";

export default {
    props: {
        availableLanguages: Array,
        availableBlocks: Array,
        baseLanguage: String,
        submitRoute: String,
        productsSearchRoute: String,
        backRoute: String,
        categories: {
            type: Array,
            default: [],
        },

        //data
        selectedCategory: {
            type: Number,
            default: null,
        },
        articleName: {
            type: Object,
            default: {},
        },
        articleSlug: {
            type: String,
            default: '',
        },
        articleSubTitle: {
            type: Object,
            default: {},
        },
        heroImage: {
            type: String,
            default: null,
        },
        metaTitle: {
            type: Object,
            default: {}
        },
        metaDescription: {
            type: Object,
            default: {}
        },
        metaKeywords: {
            type: Object,
            default: {}
        },


        dynamicContent: {
            type: Array,
            default: [],
        },
    },
    data() {
        return {
            selectedLanguage: this.baseLanguage,
            slugData: '',
            articleNameData: {},
            errors: {},
        }
    },
    beforeMount() {
        this.slugData = this.articleSlug;
    },
    computed: {
        articleNameOnBaseLanguage() {
            return this.articleNameData[this.baseLanguage];
        }
    },
    watch: {
        articleNameOnBaseLanguage(newArticleName, oldArticleName) {
            this.slugData = slug(newArticleName, {locale: this.baseLanguage});
        }
    },
    methods: {
        handleSubmit(event) {
            this.errors = [];

            const formData = new FormData(event.target);

            axios.post(this.submitRoute, formData, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
                .then(function (response) {
                    if (response.data.data.hasOwnProperty('redirect_to')  && response.data.data.redirect_to !== '') {
                        window.location.href = response.data.data.redirect_to;
                    }
                }).catch(error => {
                    if (error.response && error.response.status === 422) {
                        this.errors = error.response.data.errors;
                    }
                })
        },
    }
}
</script>

<template>
    <div class="card shadow mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <strong class="card-title m-0">{{ $t('admin.blog_article_information') }}</strong>
            <language-switcher-component v-model="selectedLanguage" :available-languages="availableLanguages" />
        </div>
        <div class="card-body">
            <form method="POST" :action="submitRoute" @submit.prevent="handleSubmit">
                <div class="form-group mb-3">
                    <select-component
                        :model-value="selectedCategory"
                        :title="$t('admin.category')"
                        :options="categories"
                        name="category_id"
                        :is-required="true"
                        label="name"
                        value-prop="id"
                        :errors="errors"
                    />
                </div>


                <multi-language-input-component
                    :title="$t('admin.name')"
                    name="name"
                    :is-required="true"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :init-data="articleName"
                    v-model="articleNameData"
                    :key="'article-name'"
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
                    :title="$t('admin.meta_title')"
                    name="meta_title"
                    :is-required="false"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :init-data="metaTitle"
                    :key="'article-meta-title'"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.meta_description')"
                    name="meta_description"
                    :is-required="false"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :init-data="metaDescription"
                    :key="'article-meta-description'"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.meta_keywords')"
                    name="meta_keywords"
                    :is-required="false"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :init-data="metaKeywords"
                    :key="'article-meta-keywords'"
                    :errors="errors"
                />

                <multi-language-text-area-component
                    :title="$t('admin.blog_article_sub_title')"
                    name="sub_title"
                    :is-required="true"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :init-data="articleSubTitle"
                    :key="'article-subtitle'"
                    :errors="errors"
                />
                <image-file-input-component
                    :title="$t('admin.blog_article_hero_image')"
                    name="hero_image"
                    image-deleted-name="hero_image_deleted"
                    :is-required="true"
                    :init-data="heroImage"
                    :key="'hero-image'"
                    :errors="errors"
                />

                <p>
                    <strong>
                        {{ $t('admin.blog_article_blocks_builder') }}
                    </strong>
                </p>
                <div class="row">
                    <div class="col-md-12">
                        <blog-article-blocks-container
                            :selected-language="selectedLanguage"
                            :available-blocks="availableBlocks"
                            :available-languages="availableLanguages"
                            :products-search-route="productsSearchRoute"
                            :dynamic-content="dynamicContent"
                            :errors="errors"
                        />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 text-right">
                        <a :href="backRoute" class="btn btn-secondary mr-1">{{ $t('admin.back') }}</a>
                        <button type="submit" class="btn btn-dark">{{ $t('admin.save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
