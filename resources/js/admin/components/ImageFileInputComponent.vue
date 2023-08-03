<script>
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
        initData: {
            type: String,
            default: null,
        },
        errors: {
            type: Object,
            default: {},
        },
    },
    data() {
        return {
            imagePreview: null,
            newImageFile: Object,
            imageDeleted: false,
        }
    },
    beforeMount() {
        if (this.initData) {
            this.imagePreview = this.initData;
        }
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
        }
    }
}
</script>

<template>
    <div class="row">
        <div class="col-md-12">
            <!-- image start -->
            <div class="form-group mb-3">
                <div class="row">
                    <div class="col-md-12">
                        <label :for="name + '-input'">{{ title }}<strong v-if="isRequired" class="text-danger">*</strong></label>
                    </div>
                </div>
                <div class="row" v-if="imagePreview">
                    <div class="col-md-12">
                        <img :src="imagePreview" :id="name + '-preview'" alt="image" class="category-img rounded mb-3">
                    </div>
                </div>
                <div class="row" v-if="imagePreview">
                    <div class="col">
                        <a href="#" class="btn mb-2 btn-danger" @click.prevent="deleteImage">
                            <span class="fe fe-trash-2 fe-16 mr-2"></span>
                            {{ $t('admin.delete') }}
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="custom-file">
                            <input type="hidden" :name="imageDeletedName" :value="imageDeletedBooleanConverted"/>
                            <input ref="fileInput" type="file" class="custom-file-input" :name="name" :id="name + '-input'" :accept="accept" @change="showNewImagePreview">
                            <label class="custom-file-label" :for="name + '-input'">{{  $t('admin.choose_file') }}</label>
                            <div class="mt-1 text-danger">
                                <template v-for="errorsByField in errorsToDisplay">
                                    <p v-for="error in errorsByField">{{ error }}</p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- image end -->
        </div>
    </div>
</template>
