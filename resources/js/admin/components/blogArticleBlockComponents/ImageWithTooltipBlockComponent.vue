<script>
import axios from 'axios';
import { nextTick } from 'vue';

export default {

    props: {
        title: String,
        isRequired: {
            type: Boolean,
            default: false,
        },
        name: String,
        imageDeletedName: String,
        accept: {
            type: String,
            default: 'image/jpeg, image/jpg, image/png',
        },
        productsSearchRoute: String,
        content: Object,
        showDeleteButton: false,
        errors: {
            type: Object,
            default: {},
        },
    },
    emits: [
        'deleteSecondaryImage',
    ],
    data() {
        return {
            imagePreview: null,
            newImageFile: Object,
            products: [],
            productOnImage: null,
            productPointerPosition: {
                clientX: undefined,
                clientY: undefined,
                movementX: 0,
                movementY: 0,
                top: 0,
                left: 0,
            },
            imageDeleted: false,
        }
    },
    mounted() {
        if (this.content.hasOwnProperty('image_url')) {
            this.imagePreview = this.content.image_url;
        }

        if (this.content.hasOwnProperty('selected_product') && this.content.selected_product.hasOwnProperty('id') && this.content.selected_product.hasOwnProperty('text')) {
            this.products.push({ id: this.content.selected_product.id, text: this.content.selected_product.text });
            this.productOnImage = this.content.selected_product.id;
        } else {
            this.loadProducts('');
        }

        nextTick(() => {
            if (this.content.hasOwnProperty('position') && this.content.position.hasOwnProperty('top') && this.content.position.hasOwnProperty('left')) {

                const self = this;
                this.$refs.image.onload = function () {
                    self.productPointerPosition.top = self.content.position.top;
                    self.productPointerPosition.left = self.content.position.left;

                    //calculate pixel position by percent position
                    const imageSize = self.$refs.imageContainer.getBoundingClientRect();

                    self.$refs.draggableContainer.style.top = (imageSize.height / 100 * self.content.position.top) + 'px';
                    self.$refs.draggableContainer.style.left = ((imageSize.width / 100 * self.content.position.left) + 15) + 'px';
                };
            }
        });
    },
    computed: {
        errorsToDisplay() {
            let errors = [];

            for (const [key, value] of Object.entries(this.errors)) {
                if (key.includes(this.name.replaceAll('[', '.').replaceAll(']', ''))) {
                    errors.push(value);
                }
            }

            return errors;
        },
        imageDeletedBooleanConverted()
        {
            return this.imageDeleted ? 1 : 0;
        }
    },
    methods: {
        showNewImagePreview(event) {
            try {
                const file = event.target.files[0];
                this.imagePreview = URL.createObjectURL(file);
            } catch (e) {
                console.error(e);
            }

            this.imageDeleted = false;
        },
        deleteImage() {
            this.$refs.fileInput.value = null;
            this.imagePreview = null;
            this.imageDeleted = true;
        },
        deleteSecondaryImage()
        {
            this.$emit('deleteSecondaryImage');
        },
        loadProducts(query) {
            axios.get(this.productsSearchRoute + '?search=' + query).then((result) => {
                this.products = result.data.data;
            }).catch(() => {
                this.products = [];
            });
        },
        //pointer methods
        dragMouseDown(event) {
            event.preventDefault()
            // get the mouse cursor position at startup:
            this.productPointerPosition.clientX = event.clientX;
            this.productPointerPosition.clientY = event.clientY;
            document.onmousemove = this.elementDrag;
            document.onmouseup = this.closeDragElement;
        },
        elementDrag(event) {
            event.preventDefault()
            this.productPointerPosition.movementX = this.productPointerPosition.clientX - event.clientX;
            this.productPointerPosition.movementY = this.productPointerPosition.clientY - event.clientY;
            this.productPointerPosition.clientX = event.clientX;
            this.productPointerPosition.clientY = event.clientY;

            const newTop = (this.$refs.draggableContainer.offsetTop - this.productPointerPosition.movementY);
            const newLeft = (this.$refs.draggableContainer.offsetLeft - this.productPointerPosition.movementX);
            const imageSize = this.$refs.imageContainer.getBoundingClientRect();

            if ((newTop <= imageSize.height - 35 && newTop >= 0) && (newLeft <= imageSize.width - 20 && newLeft >= 15)) {
                // set the element's new position:
                this.$refs.draggableContainer.style.top = newTop + 'px';
                this.$refs.draggableContainer.style.left = newLeft  + 'px';

                this.productPointerPosition.top = (newTop / imageSize.height * 100).toFixed(0);
                this.productPointerPosition.left = ((newLeft - 15) / imageSize.width * 100).toFixed(0);
            }

        },
        closeDragElement() {
            document.onmouseup = null
            document.onmousemove = null
        }
    }
}
</script>

<template>
    <div class="col-md-6">
        <!-- image start -->
        <div class="form-group mb-3">
            <div class="row">
                <div class="col-md-12">
                    <label :for="name + '-input'">{{ title }}</label>
                </div>
            </div>
            <div class="row" v-if="imagePreview">
                <div class="col-md-12 mb-3">
                    <div class="blog-article-image-container" ref="imageContainer">
                        <img class="blog-article-image-preview" :src="imagePreview" alt="image" ref="image"/>
                        <div class="blog-article-image-product-pointer" :class="{'d-none': !productOnImage}" @mousedown="dragMouseDown" ref="draggableContainer" style="top: 0; left: 15px;"></div>
                    </div>
                </div>
            </div>
            <div class="row" v-if="imagePreview">
                <div class="col">
                    <a href="#" class="btn mb-2 btn-danger" @click.prevent="deleteImage">
                        <span class="fe fe-trash-2 fe-16 mr-2"></span>
                        {{ $t('admin.delete_image') }}
                    </a>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <div class="custom-file">
                        <input type="hidden" :name="imageDeletedName" :value="imageDeletedBooleanConverted"/>
                        <input ref="fileInput" type="file" class="custom-file-input" :name="name + '[image]'" :id="name + '-input'" :accept="accept" @change="showNewImagePreview">
                        <input v-if="productOnImage" type="hidden" :name="name + '[top]'" v-model="productPointerPosition.top">
                        <input v-if="productOnImage" type="hidden" :name="name + '[left]'" v-model="productPointerPosition.left">
                        <label class="custom-file-label" :for="name + '-input'">{{  $t('admin.choose_file') }}</label>
                        <div class="mt-1 text-danger">
                            <template v-for="errorsByField in errorsToDisplay">
                                <p v-for="error in errorsByField">{{ error }}</p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <div class="form-group mb-1">
                        <input type="hidden" :name="name + '[product_id]'" v-model="productOnImage">

                    </div>
                </div>
            </div>
            <div class="row mb-3" v-if="showDeleteButton">
                <div class="col">
                    <a href="#" class="btn mb-2 btn-danger" @click.prevent="deleteSecondaryImage">
                        <span class="fe fe-trash-2 fe-16 mr-2"></span>
                        {{ $t('admin.delete_additional_image') }}
                    </a>
                </div>
            </div>
        </div>
        <!-- image end -->
    </div>
</template>
