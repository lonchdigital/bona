<script>
export default {
    props: {
        title: String,

        modelValue: null,
        name: String,


        selectedColor: Number,
        allColors: {
            type: Array,
            default: [],
        },
        index: {
            type: Number,
            default: 0,
        },

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
        'deleteColor',
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
    <div class="row1 art-repeater-row" :key="selectedColor">

        <div class="row1">
            <div class="col">
                <input type="hidden" v-if="selectedColor !== null" :name="'all_color_ids[' + index + '][id]'" :value="selectedColor" >

                <select-component
                    :is-multi-select="false"
                    :model-value="selectedColor.id"
                    :title="$t('admin.product_colors')"
                    :options="allColors"
                    label="text"
                    value-prop="id"
                    :name="'all_color_ids[' + index + '][color_id]'"
                    :is-required="false"
                    :errors="errors"
                />

                <input-component
                    :title="'Price'"
                    :name="'all_color_ids[' + index + '][price]'"
                    :model-value="selectedColor.price"
                    :errors="errors"
                    :is-required="true"
                />


<!--                <multi-language-input-component
                    :title="$t('admin.characteristic_name')"
                    :name="'characteristics[' + index + '][name]'"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="true"
                    :init-data="characteristic.hasOwnProperty('name') ? characteristic.name : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.characteristic_value')"
                    :name="'characteristics[' + index + '][value]'"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="true"
                    :init-data="characteristic.hasOwnProperty('value') ? characteristic.value : []"
                    :errors="errors"
                />-->

            </div>

        </div>

        <div class="col mt-2">
            <div class="row">
                <div class="col">
                    <a href="#" id="add-option" class="btn mb-2 btn-danger" @click.prevent="() => $emit('deleteColor', index)"><span class="fe fe-trash fe-16 mr-2"></span>{{ $t('admin.color_delete')}}</a>
                </div>
            </div>
        </div>

    </div>
</template>
