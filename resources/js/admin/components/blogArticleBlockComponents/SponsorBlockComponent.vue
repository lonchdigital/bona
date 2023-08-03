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
        selectedLanguage: String,
        availableLanguages: Array,
        errors: {
            type: Object,
            default: [],
        }
    },
    data() {
        return {
            sponsorLink: '',
        }
    },
    mounted() {
        if (this.content.hasOwnProperty('sponsor_link')) {
            this.sponsorLink = this.content.sponsor_link;
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
    <input type="hidden" :name="name + '[type_id]'" value="4">
    <input type="hidden" :name="name + '[id]'" :value="blockId">
    <div class="row blog-article-block mb-3">
        <div class="col mt-2">
            <div class="row">
                <div class="col">
                    <p>
                        <strong>
                            {{ $t('admin.blog_article_block_sponsor') }}
                        </strong>
                    </p>
                </div>
            </div>
            <image-file-input-component
                :init-data="content.hasOwnProperty('sponsor_image_url') ? content.sponsor_image_url : null"
                :title="$t('admin.sponsor_image')"
                :name="name + '[sponsor_image]'"
                :image-deleted-name="name + '[sponsor_image_deleted]'"
                :is-required="true"
                :errors="errors"

            />
            <multi-language-text-area-component
                :init-data="content.hasOwnProperty('sponsor_text') ? content.sponsor_text : []"
                :title="$t('admin.sponsor_text')"
                :name="name + '[sponsor_text]'"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="true"
                :errors="errors"
            />
            <div class="form-group mb-3">
                <input-component
                    :title="$t('admin.sponsor_link')"
                    :name="name + '[sponsor_link]'"
                    :is-required="true"
                    :errors="errors"
                    v-model="sponsorLink"
                />
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
