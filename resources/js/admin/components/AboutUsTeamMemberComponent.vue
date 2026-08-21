<script>
import MultiLanguageInputComponent from "./MultiLanguageInputComponent.vue";
import MultiLanguageTextAreaComponent from "./MultiLanguageTextAreaComponent.vue";
import ImageFileInputComponent from "./ImageFileInputComponent.vue";

export default {
    components: {MultiLanguageInputComponent, MultiLanguageTextAreaComponent, ImageFileInputComponent},
    props: {
        memberId: {type: Number, default: null},
        member: {type: Object, default: {}},
        index: {type: Number, default: 0},
        selectedLanguage: {type: String, default: 'uk'},
        availableLanguages: {type: Array, default: ['uk', 'ru']},
        errors: {type: Object, default: []},
    },
    emits: ['deleteMember'],
}
</script>

<template>
    <div class="row1 art-repeater-row" :key="memberId">
        <div class="col">
            <input type="hidden" v-if="memberId !== null" :name="'team[' + index + '][id]'" :value="memberId">

            <div class="form-group mb-3">
                <image-file-input-component
                    :title="$t('admin.about_team_photo')"
                    :name="'team[' + index + '][photo]'"
                    :is-required="false"
                    :errors="errors"
                    :init-data="member.hasOwnProperty('photo_url') ? member.photo_url : null"
                />
            </div>

            <multi-language-input-component
                :title="$t('admin.about_team_name')"
                :name="'team[' + index + '][name]'"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="false"
                :init-data="member.hasOwnProperty('name') ? member.name : []"
                :errors="errors"
            />

            <multi-language-input-component
                :title="$t('admin.about_team_role')"
                :name="'team[' + index + '][role]'"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="false"
                :init-data="member.hasOwnProperty('role') ? member.role : []"
                :errors="errors"
            />

            <multi-language-input-component
                :title="$t('admin.about_team_experience')"
                :name="'team[' + index + '][experience]'"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="false"
                :init-data="member.hasOwnProperty('experience') ? member.experience : []"
                :errors="errors"
            />

            <multi-language-text-area-component
                :title="$t('admin.about_team_quote')"
                :name="'team[' + index + '][quote]'"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="false"
                :init-data="member.hasOwnProperty('quote') ? member.quote : []"
                :errors="errors"
            />

            <div class="row">
                <div class="col">
                    <a href="#" class="btn mb-2 btn-danger" @click.prevent="() => $emit('deleteMember', index)">
                        <span class="fe fe-trash fe-16 mr-2"></span>{{ $t('admin.delete') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
