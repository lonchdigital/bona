<script>
export default {
    props: {
        name: {
            type: String,
            default: '',
        },
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
            default: [],
        },
        selectedLanguage: String,
        availableLanguages: Array,
    },

    mounted() {

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
    <input type="hidden" :name="name + '[type_id]'" value="3">
    <input type="hidden" :name="name + '[id]'" :value="blockId">
    <div class="row blog-article-block mb-3">
        <div class="col mt-2">
            <div class="row">
                <div class="col">
                    <p>
                        <strong>
                            {{ $t('admin.blog_article_block_quote') }}
                        </strong>
                    </p>
                </div>
            </div>
            <multi-language-text-area-component
                :title="$t('admin.blog_article_block_quote')"
                :init-data="this.content.hasOwnProperty('quote') ? this.content.quote : []"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="true"
                :name="name + '[quote]'"
                :errors="errors"
            />
            <multi-language-input-component
                :title="$t('admin.blog_article_block_quote_author')"
                :init-data="content.hasOwnProperty('quote_author') ? content.quote_author : []"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :name="name + '[quote_author]'"
                :errors="errors"
            />
            <multi-language-input-component
                :title="$t('admin.blog_article_block_quote_author_position')"
                :init-data="content.hasOwnProperty('quote_author_position') ? content.quote_author_position : []"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :name="name + '[quote_author_position]'"
                :errors="errors"
            />
            <image-file-input-component
                :title="$t('admin.blog_article_block_quote_author_image')"
                :init-data="content.hasOwnProperty('quote_author_image_url') ? content.quote_author_image_url : ''"
                :name="name + '[quote_author_image]'"
                :image-deleted-name="name + '[quote_author_image_deleted]'"
                :errors="errors"
            />

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
