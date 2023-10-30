<script>
export default {
    props: {
        title: String,

        modelValue: null,
        name: String,

        modelValueSelected: null,
        nameSelected: String,

        label: String,
        valueProp: String,
        options: {
            type: Array,
            default: [],
        },
        searchable: {
            type: Boolean,
            default: false,
        },
        isRequired: {
            type: Boolean,
            default: false,
        },
        isMultiSelect: {
            type: Boolean,
            default: false,
        },
        maxItems: {
            type: Number,
            default: -1,
        },
        errors: {
            type: Object,
            default: {},
        },
    },
    data() {
        return {
            value: null,
            valueSelected: null,
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
        if (this.modelValueSelected) {
            this.valueSelected = this.modelValueSelected
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

<!--    <input type="hidden" :name="name" v-model="value">
    <input type="hidden" :name="nameSelected" v-model="valueSelected">
    <label>{{ title }}<strong class="text-danger" v-if="isRequired">*</strong></label>
    <multiselect-component
        :mode="isMultiSelect ? 'tags' : 'single'"
        v-model="value"
        :options="options"
        :label="label"
        :value-prop="valueProp"
        :searchable="true"
        :max="maxItems"
        @update:model-value="$emit('update:modelValue', $event)"
        @search-change="(e) => $emit('searchChange', e)"
    />-->



    <div class="form-group mb-3">
        <label for="all_color_ids">{{ $t('admin.all_colors') }} <strong
            class="text-danger">*</strong></label>
        <select multiple class="form-control select2" name="all_color_ids[]" id="all_color_ids">
            <option :data-value="option.hex" :value="option.id" v-for="option in options">{{ option.name }}</option>
        </select>
        <div class="mt-1 text-danger ajaxError" id="error-field-all_color_ids"></div>
    </div>

    <div class="mt-1 text-danger">
        <template v-for="errorsByField in errorsToDisplay">
            <p v-for="error in errorsByField">{{ error }}</p>
        </template>
    </div>


</template>
