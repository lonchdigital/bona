<script>
export default {
    props: {
        title: String,
        isRequired: Boolean,
        name: String,
        modelValue: Boolean,
        errors: {
            type: Object,
            default: () => ({}),
        }
    },
    emits: ['update:modelValue'],
    computed: {
        errorsToDisplay() {
            return Object.entries(this.errors)
                .filter(([key]) => key.includes(this.name.replaceAll('[', '.').replaceAll(']', '')))
                .map(([, value]) => value);
        }
    },
    methods: {
        handleModelValueUpdate(event) {
            const value = event.target.checked;
            this.$emit('update:modelValue', value);
        }
    }
}
</script>

<template>
    <div class="custom-control custom-checkbox mb-3">
        <input type="hidden" :name="name" :value="0">
        <input
            class="custom-control-input"
            type="checkbox"
            :id="name"
            :name="name"
            :checked="modelValue"
            :value="1"
            @change="handleModelValueUpdate"
        >
        <label class="custom-control-label" :for="name">
            {{ title }} <strong v-if="isRequired" class="text-danger">*</strong>
        </label>
        <div class="mt-1 text-danger" v-if="errorsToDisplay.length">
            <p v-for="error in errorsToDisplay" :key="error">{{ error }}</p>
        </div>
    </div>
</template>
