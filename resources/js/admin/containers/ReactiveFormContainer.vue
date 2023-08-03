<script>
import axios from "axios";

export default {
    props: {
        submitRoute: {
            type: String,
            default: '',
        },
        backRoute: {
            type: String,
            default: '',
        },
        baseLanguage: {
            type: String,
            default: 'uk',
        },
        availableLanguages: {
            type: Array,
            default: ['uk', 'ru'],
        },
        cardBodyTitle: {
            type: String,
            default: '',
        }
    },
    emits: [
        'onSelectedLanguageChange',
        'onErrorsChange',
    ],
    data() {
        return {
            selectedLanguage: this.baseLanguage,
            errors: [],
        };
    },
    watch: {
        errors(errorsNew) {
            this.$emit('onErrorsChange', errorsNew);
        }
    },
    methods: {
        submitForm() {
            this.errors = [];

            const formData = new FormData(event.target);

            axios.post(this.submitRoute, formData, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
                .then(function (response) {
                    if (response.hasOwnProperty('data') && response.data.hasOwnProperty('data') && response.data.data.hasOwnProperty('redirect_to')  && response.data.data.redirect_to !== '') {
                        window.location.href = response.data.data.redirect_to;
                    }
                }).catch(error => {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                }
            });
        }
    }
}
</script>

<template>
    <div class="card shadow mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <strong class="card-title m-0">{{ cardBodyTitle }}</strong>
            <language-switcher-component v-model="selectedLanguage" :available-languages="availableLanguages" @update:model-value="() => $emit('onSelectedLanguageChange', selectedLanguage)"/>
        </div>
        <div class="card-body">
            <form @submit.prevent="submitForm">
                <slot></slot>
                <div class="row">
                    <div class="col-md-12 text-right">
                        <a v-if="backRoute !== ''" :href="backRoute" class="btn btn-secondary mr-1">{{ $t('admin.back') }}</a>
                        <button type="submit" class="btn btn-dark">{{ $t('admin.save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
