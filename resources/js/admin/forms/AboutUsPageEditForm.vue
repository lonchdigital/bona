<script>

import axios from "axios";
import MultiLanguageInputComponent from "../components/MultiLanguageInputComponent.vue";
import ImageFileInputComponent from "../components/ImageFileInputComponent.vue";
import ServicesSectionsComponent from "../components/ServicesSectionsComponent.vue";
import MultiLanguageRichTextEditorComponent from "../components/MultiLanguageRichTextEditorComponent.vue";
import TextAreaComponent from "../components/TextAreaComponent.vue";
import MultiLanguageTextAreaComponent from "../components/MultiLanguageTextAreaComponent.vue";
import AboutUsFactComponent from "../components/AboutUsFactComponent.vue";
import AboutUsStepComponent from "../components/AboutUsStepComponent.vue";
import AboutUsTeamMemberComponent from "../components/AboutUsTeamMemberComponent.vue";
import * as transliteration from 'transliteration';


export default {
    components: {
        MultiLanguageInputComponent,
        ImageFileInputComponent,
        ServicesSectionsComponent,
        MultiLanguageRichTextEditorComponent,
        TextAreaComponent,
        MultiLanguageTextAreaComponent,
        AboutUsFactComponent,
        AboutUsStepComponent,
        AboutUsTeamMemberComponent
    },
    props: {
        submitRoute: {
            type: String,
            default: '',
        },
        backRoute: {
            type: String,
            default: '',
        },
        availableLanguages: {
            type: Array,
            default: ['uk', 'ru'],
        },
        baseLanguage: {
            type: String,
            default: 'uk',
        },

        factsTitle: {type: Object, default: {}},
        historyTitle: {type: Object, default: {}},
        historyText: {type: Object, default: {}},
        stepsTitle: {type: Object, default: {}},
        teamTitle: {type: Object, default: {}},
        ctaTitle: {type: Object, default: {}},
        ctaText: {type: Object, default: {}},
        ctaButtonText: {type: Object, default: {}},
        ctaButtonUrl: {type: String, default: ''},

        pageFacts: {type: Array, default: () => []},
        pageSteps: {type: Array, default: () => []},
        pageTeam: {type: Array, default: () => []},

        pageMetaTitle: {
            type: Object,
            default: {},
        },
        pageMetaDescription: {
            type: Object,
            default: {},
        },
        pageMetaKeywords: {
            type: Object,
            default: {},
        },
        pageMetaTags: {
            type: String,
            default: '',
        },

        title: {
            type: Array,
            default: [],
        },
        description: {
            type: Array,
            default: [],
        },
        buttonText: {
            type: Array,
            default: [],
        },
        buttonUrl: {
            type: String,
            default: '',
        },
        imageUrl: {
            type: String,
            default: '',
        },
        videoIframe: {
            type: String,
            default: '',
        },

    },
    data() {
        return {
            selectedLanguage: '',
            selectedFieldId: null,
            errors: [],
            facts: [],
            steps: [],
            team: [],
        }
    },
    created() {

    },
    mounted() {
        this.selectedLanguage = this.baseLanguage;
        this.facts = this.pageFacts ? [...this.pageFacts] : [];
        this.steps = this.pageSteps ? [...this.pageSteps] : [];
        this.team = this.pageTeam ? [...this.pageTeam] : [];

    },
    computed: {
        /*slug() {
            return transliteration.slugify(this.produktName);
        }*/
    },
    watch: {
        selectedFieldId() {
            this.selectedOptions = [];
        }
    },
    methods: {
        addFact() { this.facts.push({}); },
        deleteFact(index) { this.facts.splice(index, 1); },
        addStep() { this.steps.push({}); },
        deleteStep(index) { this.steps.splice(index, 1); },
        addMember() { this.team.push({}); },
        deleteMember(index) { this.team.splice(index, 1); },


        changeSelectedLanguage(newSelectedLanguage) {
            this.selectedLanguage = newSelectedLanguage;
        },
        handleFormSubmit(errors) {
            this.errors = errors;
        },

    }

}
</script>

<template>
    <reactive-form-container
        :submit-route="submitRoute"
        :back-route="backRoute"
        @on-selected-language-change="changeSelectedLanguage"
        @on-errors-change="handleFormSubmit"
        :card-body-title="$t('admin.edit_page') "
    >
        <div class="row">
            <div class="col">

                <multi-language-input-component
                    :title="$t('admin.meta_title')"
                    name="meta_title"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="pageMetaTitle"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.meta_description')"
                    name="meta_description"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="pageMetaDescription"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.meta_keywords')"
                    name="meta_keywords"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="pageMetaKeywords"
                    :errors="errors"
                />


                <multi-language-input-component
                    :title="$t('admin.title')"
                    name="title"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data=" (title) ? title : []"
                    :errors="errors"
                />

                <multi-language-rich-text-editor-component
                    :title="$t('admin.description')"
                    name="description"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :content="(description) ? description : []"
                    :errors="errors"
                />

                <multi-language-input-component
                    :title="$t('admin.text_button')"
                    name="button_text"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="(buttonText) ? buttonText : []"
                    :errors="errors"
                />

                <div class="form-group mb-3">
                    <input-component
                        :title="$t('admin.button_link')"
                        name="button_url"
                        :model-value="buttonUrl"
                        :errors="errors"
                        :is-required="false"
                    />
                </div>

                <image-file-input-component
                    :title="$t('admin.image')"
                    name="image"
                    image-deleted-name="image_deleted"
                    :is-required="false"
                    :errors="errors"
                    :init-data="(imageUrl) ? imageUrl : null"
                />

                <div class="form-group mb-3">
                    <input-component
                        title="iframe"
                        name="iframe"
                        :model-value="videoIframe"
                        :errors="errors"
                        :is-required="false"
                    />
                </div>

                <text-area-component
                    :title="$t('admin.meta_tags')"
                    name="meta_tags"
                    :is-required="false"
                    :init-data="pageMetaTags"
                    :errors="errors"
                />

            </div>
        </div>

        <hr>
        <p><strong>{{ $t('admin.about_facts') }}</strong></p>

        <multi-language-input-component
            :title="$t('admin.about_section_title')"
            name="facts_title"
            :selected-language="selectedLanguage"
            :available-languages="availableLanguages"
            :is-required="false"
            :init-data="factsTitle"
            :errors="errors"
        />

        <div class="form-group mb-3 art-admin-repeater-four-width">
            <about-us-fact-component
                v-for="(fact, index) in facts"
                :key="index"
                :fact-id="fact.hasOwnProperty('id') ? fact.id : null"
                :fact="fact"
                :index="index"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :errors="errors"
                @delete-fact="() => deleteFact(index)"
            />
        </div>
        <div class="row"><div class="col">
            <a href="#" class="btn mb-2 btn-secondary" @click.prevent="addFact">
                <span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.about_fact_add') }}
            </a>
        </div></div>

        <hr>
        <p><strong>{{ $t('admin.about_history') }}</strong></p>

        <multi-language-input-component
            :title="$t('admin.about_section_title')"
            name="history_title"
            :selected-language="selectedLanguage"
            :available-languages="availableLanguages"
            :is-required="false"
            :init-data="historyTitle"
            :errors="errors"
        />

        <multi-language-rich-text-editor-component
            :title="$t('admin.about_history_text')"
            name="history_text"
            :selected-language="selectedLanguage"
            :available-languages="availableLanguages"
            :is-required="false"
            :init-data="historyText"
            :errors="errors"
        />

        <hr>
        <p><strong>{{ $t('admin.about_steps') }}</strong></p>

        <multi-language-input-component
            :title="$t('admin.about_section_title')"
            name="steps_title"
            :selected-language="selectedLanguage"
            :available-languages="availableLanguages"
            :is-required="false"
            :init-data="stepsTitle"
            :errors="errors"
        />

        <div class="form-group mb-3 art-admin-repeater-four-width">
            <about-us-step-component
                v-for="(step, index) in steps"
                :key="index"
                :step-id="step.hasOwnProperty('id') ? step.id : null"
                :step="step"
                :index="index"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :errors="errors"
                @delete-step="() => deleteStep(index)"
            />
        </div>
        <div class="row"><div class="col">
            <a href="#" class="btn mb-2 btn-secondary" @click.prevent="addStep">
                <span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.about_step_add') }}
            </a>
        </div></div>

        <hr>
        <p><strong>{{ $t('admin.about_team') }}</strong></p>

        <multi-language-input-component
            :title="$t('admin.about_section_title')"
            name="team_title"
            :selected-language="selectedLanguage"
            :available-languages="availableLanguages"
            :is-required="false"
            :init-data="teamTitle"
            :errors="errors"
        />

        <div class="form-group mb-3 art-admin-repeater-four-width">
            <about-us-team-member-component
                v-for="(member, index) in team"
                :key="index"
                :member-id="member.hasOwnProperty('id') ? member.id : null"
                :member="member"
                :index="index"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :errors="errors"
                @delete-member="() => deleteMember(index)"
            />
        </div>
        <div class="row"><div class="col">
            <a href="#" class="btn mb-2 btn-secondary" @click.prevent="addMember">
                <span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.about_team_add') }}
            </a>
        </div></div>

        <hr>
        <p><strong>{{ $t('admin.about_cta') }}</strong></p>

        <multi-language-input-component
            :title="$t('admin.about_section_title')"
            name="cta_title"
            :selected-language="selectedLanguage"
            :available-languages="availableLanguages"
            :is-required="false"
            :init-data="ctaTitle"
            :errors="errors"
        />

        <multi-language-text-area-component
            :title="$t('admin.about_cta_text')"
            name="cta_text"
            :selected-language="selectedLanguage"
            :available-languages="availableLanguages"
            :is-required="false"
            :init-data="ctaText"
            :errors="errors"
        />

        <multi-language-input-component
            :title="$t('admin.about_cta_button')"
            name="cta_button_text"
            :selected-language="selectedLanguage"
            :available-languages="availableLanguages"
            :is-required="false"
            :init-data="ctaButtonText"
            :errors="errors"
        />

        <div class="form-group mb-3">
            <input-component
                :title="$t('admin.about_cta_url')"
                name="cta_button_url"
                :model-value="ctaButtonUrl"
                :errors="errors"
            />
        </div>

    </reactive-form-container>
</template>
