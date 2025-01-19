<script>
import axios from "axios";
import MultiLanguageInputComponent from "./MultiLanguageInputComponent.vue";
import DateInputComponent from "../components/DateInputComponent.vue";
import SelectComponent from "../components/SelectComponent.vue";

export default {
    components: {MultiLanguageInputComponent, DateInputComponent, SelectComponent},
    props: {
        testimonialId: {
            type: Number,
            default: null,
        },
        testimonial: {
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
        ratingOptions: {
            type: Object,
            default: {},
        },
        errors: {
            type: Object,
            default: [],
        }
    },
    emits: [
        'deleteSlide',
    ],
    data () {
        return {
            slideDescription: [],
        }
    },
}
</script>

<template>
    <div class="row1 art-repeater-row" :key="testimonialId">
        <div class="col">
            <div class="row mb-4">
                <div class="col">
                    <input type="hidden" v-if="testimonialId !== null" :name="'testimonials[' + index + '][id]'" :value="testimonialId">


                    <image-file-input-component
                        :title="$t('admin.image')"
                        :name="'testimonials[' + index + '][image]'"
                        :image-deleted-name="'testimonials[' + index + '][image_deleted]'"
                        :is-required="true"
                        :errors="errors"
                        :init-data="testimonial.hasOwnProperty('testimonial_image_url') ? testimonial.testimonial_image_url : null"
                    />

                    <multi-language-input-component
                        :title="$t('admin.testimonial_name')"
                        :name="'testimonials[' + index + '][name]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="true"
                        :init-data="testimonial.hasOwnProperty('name') ? testimonial.name : []"
                        :errors="errors"
                    />

                    <multi-language-input-component
                        :title="$t('admin.testimonials_review')"
                        :name="'testimonials[' + index + '][review]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="true"
                        :init-data="testimonial.hasOwnProperty('review') ? testimonial.review : []"
                        :errors="errors"
                    />


                    <date-input-component
                        :is-multi-select="false"
                        :model-value="testimonial.hasOwnProperty('date') ? testimonial.date : ''"
                        :title="$t('admin.date')"
                        label="date"
                        value-prop="id"
                        :name="'testimonials[' + index + '][date]'"
                        :is-required="false"
                        :errors="errors"
                    />

                    <select-component
                        :is-multi-select="false"
                        :model-value="testimonial.hasOwnProperty('rating') ? testimonial.rating : 5"
                        :title="$t('admin.rating')"
                        :options="ratingOptions"
                        label="text"
                        value-prop="id"
                        :name="'testimonials[' + index + '][rating]'"
                        :is-required="false"
                        :errors="errors"
                    />

                    <div class="form-group mt-3">
                        <input-component
                            :title="$t('admin.link')"
                            :name="'testimonials[' + index + '][url]'"
                            :model-value="testimonial.hasOwnProperty('url') ? testimonial.url : []"
                            :errors="errors"
                            :is-required="false"
                        />
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col">
                    <a href="#" id="add-option" class="btn mb-2 btn-danger" @click.prevent="() => $emit('deleteTestimonial', index)"><span class="fe fe-trash fe-16 mr-2"></span>{{ $t('admin.delete')}}</a>
                </div>
            </div>
        </div>
    </div>
</template>
