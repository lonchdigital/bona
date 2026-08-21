<script>
import MultiLanguageInputComponent from "./MultiLanguageInputComponent.vue";
import ImageFileInputComponent from "./ImageFileInputComponent.vue";
import InputComponent from "./InputComponent.vue";

export default {
    components: {MultiLanguageInputComponent, ImageFileInputComponent, InputComponent},
    props: {
        certificateId: {
            type: Number,
            default: null,
        },
        certificate: {
            type: Object,
            default: {},
        },
        index: {
            type: Number,
            default: 0,
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
        },
    },
    emits: [
        'deleteCertificate',
    ],
}
</script>

<template>
    <div class="row1 art-repeater-row" :key="certificateId">
        <div class="col">

            <input type="hidden" v-if="certificateId !== null" :name="'certificate[' + index + '][id]'" :value="certificateId">

            <div class="form-group mb-3">
                <image-file-input-component
                    :title="$t('admin.author_certificate_image')"
                    :name="'certificate[' + index + '][image]'"
                    :is-required="certificateId === null"
                    :errors="errors"
                    :init-data="certificate.hasOwnProperty('image_url') ? certificate.image_url : null"
                />
            </div>

            <multi-language-input-component
                :title="$t('admin.author_certificate_title')"
                :name="'certificate[' + index + '][title]'"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="false"
                :init-data="certificate.hasOwnProperty('title') ? certificate.title : []"
                :errors="errors"
            />

            <div class="form-group mb-3">
                <input-component
                    :title="$t('admin.author_certificate_issuer')"
                    :name="'certificate[' + index + '][issuer]'"
                    :model-value="certificate.hasOwnProperty('issuer') ? certificate.issuer : ''"
                    :errors="errors"
                />
            </div>

            <div class="form-group mb-3">
                <input-component
                    :title="$t('admin.author_certificate_year')"
                    :name="'certificate[' + index + '][issued_year]'"
                    type="number"
                    :model-value="certificate.hasOwnProperty('issued_year') ? certificate.issued_year : ''"
                    :errors="errors"
                />
            </div>

            <div class="row">
                <div class="col">
                    <a href="#" class="btn mb-2 btn-danger" @click.prevent="() => $emit('deleteCertificate', index)">
                        <span class="fe fe-trash fe-16 mr-2"></span>{{ $t('admin.delete') }}
                    </a>
                </div>
            </div>

        </div>
    </div>
</template>
