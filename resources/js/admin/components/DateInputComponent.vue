<script>
export default {
    props: {
        title: String,

        modelValue: null,
        name: String,

        label: String,
        valueProp: String,
        isRequired: {
            type: Boolean,
            default: false,
        },
        errors: {
            type: Object,
            default: {},
        },
    },
    data() {
        return {
            value: null,
        };
    },
    emits: [
        'searchChange',
        'update:modelValue',
    ],
    watch: {
        modelValue(newValue) {
            this.value = newValue;
        }
    },
    mounted() {
        if (this.modelValue) {
            this.value = this.modelValue
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
        }
    },

}
</script>

<template>
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="form-group mb-1">

                <label>{{ title }}<strong class="text-danger" v-if="isRequired">*</strong></label>
                <input type="date" class="form-control" :name="name" :value="modelValue">

                <div class="mt-1 text-danger">
                    <template v-for="errorsByField in errorsToDisplay">
                        <p v-for="error in errorsByField">{{ error }}</p>
                    </template>
                </div>

            </div>
        </div>
    </div>
</template>
