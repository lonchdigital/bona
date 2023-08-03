<script>
export default {
    props: {
        title: String,
        isRequired: Boolean,
        name: String,
        modelValue: String | Number,
        type: {
            type: String,
            default: 'text',
        },
        errors: {
            type: Object,
            default: {},
        },
    },
    data() {
        return {
            value: null,
        }
    },
    emits: [
        'update:modelValue'
    ],
    watch: {
        modelValue: {
            handler(newValue) {
                this.value = newValue;
            }
        }
    },
    beforeMount() {
        this.value = this.modelValue;
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
    methods: {
        handleModelValueUpdate(value) {
            this.value = value;
            this.$emit('update:modelValue', value);
        }
    }
}
</script>

<template>
    <label :for="name + '-sponsor-link'">{{ title }} <strong v-if="isRequired" class="text-danger">*</strong></label>
    <input :type="type" :id="name + '-sponsor-link'" :name="name" class="form-control" :value="value" @input="handleModelValueUpdate($event.target.value)">
    <div class="mt-1 text-danger">
        <template v-for="errorsByField in errorsToDisplay">
            <p v-for="error in errorsByField">{{ error }}</p>
        </template>
    </div>
</template>
