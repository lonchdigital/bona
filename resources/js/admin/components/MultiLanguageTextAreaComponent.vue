<script>
export default {
    props: {
        selectedLanguage: String,
        availableLanguages: Array,
        initData: {
            type: Object,
            default: [],
        },
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
        prepareInitialData() {
            let preparedInitialData = this.initData;
            for(const availableLanguage of this.availableLanguages) {
                if (!preparedInitialData.hasOwnProperty(availableLanguage)) {
                    preparedInitialData[availableLanguage] = '';
                }
            }

            return preparedInitialData;
        },
        errorsToDisplay() {
            let errors = [];

            for (const [key, value] of Object.entries(this.errors)) {
                const name = this.name.replaceAll('[', '.').replaceAll(']', '');
                this.availableLanguages.forEach(function (availableLanguage) {
                    if (key.includes(name + '.' + availableLanguage)) {
                        errors.push(value);
                    }
                });
            }
            return errors;
        }
    },
    data() {
        return {
            inputData: Array,
        }
    },
    beforeMount() {
        this.inputData = this.prepareInitialData;
    }
}
</script>

<template>
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="tab-content" v-for="availableLanguage in availableLanguages">
                <div class="multilang-content tab-pane fade" :class="{'active show': availableLanguage === selectedLanguage}">
                    <div class="form-group mb-1">
                        <label :for="name + '-' + availableLanguage">{{ title }}
                            <strong>{{ availableLanguage.toUpperCase() }}</strong>
                            <strong v-if="isRequired" class="text-danger">*</strong>
                        </label>
                        <textarea :id="name + '-' + availableLanguage" type="text" :name="name + '[' + availableLanguage + ']'" class="form-control" v-model="inputData[availableLanguage]"></textarea>
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

