<script>

export default {
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
        initData: {
            type: Array,
            default: [],
        }
    },
    data() {
        return {
            slides: [],
            selectedLanguage: '',
            errors: [],
        }
    },
    mounted() {
        this.selectedLanguage = this.baseLanguage;

        if (this.initData) {
            this.slides = this.initData;
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
    }

}
</script>

<template>
    <reactive-form-container
        :submit-route="submitRoute"
        :back-route="backRoute"
        @on-selected-language-change="changeSelectedLanguage"
        @on-errors-change="handleFormSubmit"
        :card-body-title="$t('admin.blog_article_information') "
    >
        <blog-slide-component
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
    </reactive-form-container>
</template>
