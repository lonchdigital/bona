<script>
import axios from "axios";
import MultiLanguageInputComponent from "./MultiLanguageInputComponent.vue";
import MultiLanguageTextAreaComponent from "./MultiLanguageTextAreaComponent.vue";

export default {
    components: {MultiLanguageInputComponent, MultiLanguageTextAreaComponent},
    props: {
        faqId: {
            type: Number,
            default: null,
        },
        faq: {
            type: Object,
            default: {},
        },
        index: {
            type: Number,
            default: 0,
        },
        baseLanguage: {
            type: String,
            default: 'uk',
        },
        selectedLanguage: {
            type: String,
            default: 'uk',
        },
        availableLanguages: {
            type: Array,
            default: ['uk', 'ru'],
        },
        errors: {
            type: Object,
            default: [],
        }
    },
    emits: [
        'deleteFaq',
    ],
    data () {
        return {
            slideDescription: [],
        }
    }
}
</script>

<template>
    <div class="row1 art-repeater-row" :key="faqId">
        <div class="col">
            <div class="row">
                <div class="col">
                    <input type="hidden" v-if="faqId !== null" :name="'faqs[' + index + '][id]'" :value="faqId" >

                    <multi-language-input-component
                        :title="$t('admin.faq_question')"
                        :name="'faqs[' + index + '][question]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="true"
                        :init-data="faq.hasOwnProperty('question') ? faq.question : []"
                        :errors="errors"
                    />

                    <multi-language-text-area-component
                        :title="$t('admin.faq_answer')"
                        :name="'faqs[' + index + '][answer]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="true"
                        :init-data="faq.hasOwnProperty('answer') ? faq.answer : []"
                        :errors="errors"
                    />

                </div>

            </div>
            <div class="row">
                <div class="col">
                    <a href="#" id="add-option" class="btn mb-2 btn-danger" @click.prevent="() => $emit('deleteFaq', index)"><span class="fe fe-trash fe-16 mr-2"></span>{{ $t('admin.question_delete')}}</a>
                </div>
            </div>
        </div>
    </div>
</template>
