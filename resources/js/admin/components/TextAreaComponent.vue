<script>
export default {
    props: {
        availableLanguages: Array,
        initData: String,
        title: String,
        isRequired: {
            type: Boolean,
            default: false,
        },
        name: String,
        errors: {
            type: Object,
            default: {},
        }
    },
    computed: {
        errorsToDisplay() {
            let errors = [];

            return errors;
        }
    },
    data() {
        return {
            inputData: this.initData,
        }
    },
}
</script>

<template>
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="tab-content">
                <div class="">
                    <div class="form-group mb-1">
                        <label :for="name">{{ title }}
                            <strong v-if="isRequired" class="text-danger">*</strong>
                        </label>
                        <textarea :id="name" type="text" :name="name" class="form-control" v-model="inputData"></textarea>
                    </div>
                </div>
            </div>
            <div class="mt-1 text-danger">
                <template v-for="errorsByField in errorsToDisplay">
                    <p v-for="error in errorsByField">{{ error }}</p>
                </template>
            </div>
        </div>
    </div>
</template>

