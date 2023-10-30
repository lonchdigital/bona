<script>
import axios from "axios";
import MultiLanguageInputComponent from "./MultiLanguageInputComponent.vue";
import MultiLanguageTextAreaComponent from "./MultiLanguageTextAreaComponent.vue";

export default {
    components: {MultiLanguageInputComponent, MultiLanguageTextAreaComponent},
    props: {
        characteristicId: {
            type: Number,
            default: null,
        },
        characteristic: {
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
        'deleteCharacteristic',
    ],
    data () {
        return {
            slideDescription: [],
        }
    }
}
</script>

<template>
    <div class="row1 art-repeater-row" :key="characteristicId">

        <div class="row1">
            <div class="col">
                <input type="hidden" v-if="characteristicId !== null" :name="'characteristics[' + index + '][id]'" :value="characteristicId" >

                <multi-language-input-component
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
                />

            </div>

        </div>

        <div class="col mt-2">
            <div class="row">
                <div class="col">
                    <a href="#" id="add-option" class="btn mb-2 btn-danger" @click.prevent="() => $emit('deleteCharacteristic', index)"><span class="fe fe-trash fe-16 mr-2"></span>{{ $t('admin.characteristic_delete')}}</a>
                </div>
            </div>
        </div>

    </div>
</template>
