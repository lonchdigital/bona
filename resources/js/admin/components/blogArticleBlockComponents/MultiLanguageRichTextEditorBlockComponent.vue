<script>
export default {
    props: {
        selectedLanguage: String,
        availableLanguages: Array,
        initData: {
            type: Array,
            default: [],
        },
        title: String,
        isRequired: {
            type: Boolean,
            default: false,
        },
        name: String,
        content: {
            type: Object,
            default: {},
        },
        blockId: {
            type: Number,
            default: null,
        },
        errors: {
            type: Object,
            default: {},
        }
    },
    emits: [
        'deleteBlock'
    ],
    methods: {
        deleteBlock() {
            this.$emit('deleteBlock');
        }
    }
}
</script>

<template>
    <input type="hidden" :name="name + '[type_id]'" value="1">
    <input type="hidden" :name="name + '[id]'" :value="blockId">
    <div class="row blog-article-block mb-3">
        <div class="col mt-2">
            <div class="row">
                <div class="col">
                    <p>
                        <strong>
                            {{ $t('admin.blog_article_block_text') }}
                        </strong>
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <multi-language-rich-text-editor-component
                        :name="name"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :title="$t('admin.blog_article_text')"
                        :content="content"
                        :errors="errors"
                    />
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <a href="#" class="btn mb-2 btn-danger" @click.prevent="deleteBlock">
                        <span class="fe fe-trash-2 fe-16 mr-2"></span>
                        {{ $t('admin.delete_block') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
