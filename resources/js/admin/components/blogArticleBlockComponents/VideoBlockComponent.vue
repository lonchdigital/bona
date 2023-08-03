

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
        }
    },
    data() {
        return {
            videoLink: '',
        }
    },
    mounted() {
        if (this.content.hasOwnProperty('video_link')) {
            this.videoLink = this.content.video_link;
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
    <input type="hidden" :name="name + '[type_id]'" value="5">
    <input type="hidden" :name="name + '[id]'" :value="blockId">
    <div class="row blog-article-block mb-3">
        <div class="col mt-2">
            <div class="row">
                <div class="col">
                    <p>
                        <strong>
                            {{ $t('admin.blog_article_block_video') }}
                        </strong>
                    </p>
                </div>
            </div>
            <div class="form-group mb-3">
                <input-component
                    :title="$t('admin.video_link')"
                    :name="name + '[video_link]'"
                    v-model="videoLink"
                    :errors="errors"
                    :is-required="true"
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
