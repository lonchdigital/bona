<script>
import MultiLanguageInputComponent from "./MultiLanguageInputComponent.vue";
import InputComponent from "./InputComponent.vue";

export default {
    components: {MultiLanguageInputComponent, InputComponent},
    props: {
        factId: {type: Number, default: null},
        fact: {type: Object, default: {}},
        index: {type: Number, default: 0},
        selectedLanguage: {type: String, default: 'uk'},
        availableLanguages: {type: Array, default: ['uk', 'ru']},
        errors: {type: Object, default: []},
    },
    emits: ['deleteFact'],
}
</script>

<template>
    <div class="row1 art-repeater-row" :key="factId">
        <div class="col">
            <input type="hidden" v-if="factId !== null" :name="'fact[' + index + '][id]'" :value="factId">

            <div class="form-group mb-3">
                <input-component
                    :title="$t('admin.about_fact_value')"
                    :name="'fact[' + index + '][value]'"
                    :model-value="fact.hasOwnProperty('value') ? fact.value : ''"
                    :errors="errors"
                />
            </div>

            <multi-language-input-component
                :title="$t('admin.about_fact_label')"
                :name="'fact[' + index + '][label]'"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="false"
                :init-data="fact.hasOwnProperty('label') ? fact.label : []"
                :errors="errors"
            />

            <div class="row">
                <div class="col">
                    <a href="#" class="btn mb-2 btn-danger" @click.prevent="() => $emit('deleteFact', index)">
                        <span class="fe fe-trash fe-16 mr-2"></span>{{ $t('admin.delete') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
