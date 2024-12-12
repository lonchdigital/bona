<script>
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css";

export default {
    props: {
        modelValue: {
            type: String,
            default: "",
        },
        name: String,
        title: String,
        isRequired: {
            type: Boolean,
            default: false,
        },
        errors: {
            type: Object,
            default: {},
        }
    },
    data() {
        return {
            datepickerInstance: null,
            inputData: this.initData,
        };
    },
    computed: {
        formattedDate() {
            return this.modelValue || "";
        },
    },
    mounted() {
        this.datepickerInstance = flatpickr(this.$refs.datepicker, {
            enableTime: true,
            dateFormat: "Y-m-d H:i:S",
            defaultDate: this.modelValue || null,
            onChange: (selectedDates, dateStr) => {
                this.$emit("update:modelValue", dateStr);
            },
        });
    },
    beforeUnmount() {
        if (this.datepickerInstance) {
            this.datepickerInstance.destroy();
        }
    },
};
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
                        <input
                            ref="datepicker"
                            :value="formattedDate"
                            type="text"
                            :name="name"
                            @input="$emit('update:modelValue', $event.target.value)"
                            class="form-control"
                        />
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
