<script>
export default {
    props: {
        name: {
            type: String,
            default: '',
        },
        productsSearchRoute: String,
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
            images: {
                type: Array,
                default: [],
            }
        }
    },
    beforeMount() {
        if (this.content.hasOwnProperty('images') && this.content.images.length) {
            this.images = this.content.images;
        } else {
            this.images = [{}];
        }
    },
    emits: [
        'deleteBlock'
    ],
    methods: {
        deleteBlock() {
            this.$emit('deleteBlock');
        },
        addImage() {
            this.images.push({});
        },
        deleteImage(index) {
            this.images.splice(index, 1);
        },
    }
}
</script>

<template>
    <input type="hidden" :name="name + '[type_id]'" value="6">
    <input type="hidden" :name="name + '[id]'" :value="blockId">
    <div class="row blog-article-block mb-3">
        <div class="col mt-2">
            <div class="row">
                <div class="col">
                    <p>
                        <strong>
                            {{ $t('admin.blog_article_block_slider') }}
                        </strong>
                    </p>
                </div>
            </div>
            <div class="row">
                <image-with-tooltip-block
                    :title="$t('admin.slide_image') + ' (' + $t('admin.blog_article_block_slider_image_requirements') + ')'"
                    v-for="(imageContent, index) in images"
                    :name="name + '[images][' + index + ']'"
                    :image-deleted-name="name + '[images][' + index + '][image_deleted]'"
                    :products-search-route="productsSearchRoute"
                    :content="imageContent"
                    :errors="errors"
                    :show-delete-button="index >= 1"
                    @delete-secondary-image="() => deleteImage(index)"
                />
                <div class="col-md-6 d-flex justify-content-center align-content-center flex-wrap">
                    <div class="row">
                        <div class="col">
                            <button class="btn btn-dark" @click.prevent="addImage">{{ $t('admin.add_image') }}</button>
                        </div>
                    </div>
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
