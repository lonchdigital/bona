<script>
import axios from "axios";
import MultiLanguageInputComponent from "./MultiLanguageInputComponent.vue";
import MultiLanguageTextAreaComponent from "./MultiLanguageTextAreaComponent.vue";

export default {
    components: {MultiLanguageInputComponent, MultiLanguageTextAreaComponent},
    props: {
        attributeId: {
            type: Number,
            default: null,
        },
        attribute: {
            type: Object,
            default: {},
        },
        attributeOptionId: {
            type: Number,
            default: 0,
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
        'deleteAttribute',
    ],
    data () {
        return {
            slideDescription: [],
        }
    }
}
</script>

<template>
    <div class="row1 art-repeater-row" :key="attributeId">

        <div class="row1">
            <div class="col">
                <input type="hidden" v-if="attributeId !== null" :name="'characteristics[' + index + '][id]'" :value="attributeId" >

                <multi-language-input-component
                    :title="$t('admin.attribute_name')"
                    :name="'attributes['+ attributeOptionId +'][' + index + '][name]'"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="true"
                    :init-data="attribute.hasOwnProperty('name') ? attribute.name : []"
                    :errors="errors"
                />

                <input-component
                    :title="$t('admin.price')"
                    :name="'attributes['+ attributeOptionId +'][' + index + '][price]'"
                    :model-value="attribute.hasOwnProperty('price') ? attribute.price : []"
                    :errors="errors"
                    :is-required="false"
                />

            </div>

        </div>

        <div class="col mt-4">
            <div class="row">
                <div class="col">
                    <a href="#" id="add-option" class="btn mb-2 btn-danger" @click.prevent="() => $emit('deleteAttribute', attribute, index, attributeOptionId)"><span class="fe fe-trash fe-16 mr-2"></span>{{ $t('admin.attribute_delete')}}</a>
                </div>
            </div>
        </div>

    </div>
</template>
