<script>
import MultiLanguageInputComponent from "./MultiLanguageInputComponent.vue";
import MultiLanguageTextAreaComponent from "./MultiLanguageTextAreaComponent.vue";

export default {
    components: {MultiLanguageInputComponent, MultiLanguageTextAreaComponent},
    props: {
        stepId: {type: Number, default: null},
        step: {type: Object, default: {}},
        index: {type: Number, default: 0},
        selectedLanguage: {type: String, default: 'uk'},
        availableLanguages: {type: Array, default: ['uk', 'ru']},
        errors: {type: Object, default: []},
    },
    emits: ['deleteStep'],
}
</script>

<template>
    <div class="row1 art-repeater-row" :key="stepId">
        <div class="col">
            <input type="hidden" v-if="stepId !== null" :name="'step[' + index + '][id]'" :value="stepId">

            <multi-language-input-component
                :title="$t('admin.about_step_title')"
                :name="'step[' + index + '][title]'"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="false"
                :init-data="step.hasOwnProperty('title') ? step.title : []"
                :errors="errors"
            />

            <multi-language-text-area-component
                :title="$t('admin.about_step_text')"
                :name="'step[' + index + '][text]'"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="false"
                :init-data="step.hasOwnProperty('text') ? step.text : []"
                :errors="errors"
            />

            <div class="row">
                <div class="col">
                    <a href="#" class="btn mb-2 btn-danger" @click.prevent="() => $emit('deleteStep', index)">
                        <span class="fe fe-trash fe-16 mr-2"></span>{{ $t('admin.delete') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
