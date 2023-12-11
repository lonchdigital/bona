<script>
export default {
    props: {
        productsSearchRoute: String,
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
    data() {
        return {
            images: [],
        }
    },
    mounted() {
        if (this.content.hasOwnProperty('images') && this.content.images.length) {
            this.images = this.content.images;
        } else {
            this.images.push({});
        }
    },
    emits: [
        'deleteBlock'
    ],
    methods: {
        deleteBlock() {
            this.$emit('deleteBlock');
        },
        addSecondImage() {
            this.images.push({});
        },
        deleteSecondaryImage() {
            if (this.images.length >= 2) {
                this.images.splice(1, 1);
            }
        }
    }
}
</script>

<template>
    <input type="hidden" :name="name + '[type_id]'" value="2">
    <input type="hidden" :name="name + '[id]'" :value="blockId">
    <div class="row blog-article-block mb-3">
        <div class="col mt-2">
            <div class="row">
                <div class="col">
                    <p>
                        <strong>
                            {{ $t('admin.blog_article_block_image_title') }}
                        </strong>
                    </p>
                </div>
            </div>
            <div class="row">
                <image-with-tooltip-block
                    :title="index >= 1 ? $t('admin.blog_article_block_image_image_2') : $t('admin.blog_article_block_image_image_1')"
                    v-for="(imageContent, index) in images"
                    :name="name + '[images][' + index + ']'"
                    :image-deleted-name="name + '[images][' + index + '][image_deleted]'"
                    :products-search-route="productsSearchRoute"
                    :content="imageContent"
                    :errors="errors"
                    :show-delete-button="index >= 1"
                    @delete-secondary-image="deleteSecondaryImage"
                />
<!--                <div v-if="images.length < 2" class="col-md-6 d-flex justify-content-center align-content-center flex-wrap">
                    <div class="row">
                        <div class="col">
                            <button class="btn btn-dark" @click.prevent="addSecondImage">{{ $t('admin.add_image') }}</button>
                        </div>
                    </div>
                </div>-->
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

