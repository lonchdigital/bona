<script>
import MultiLanguageInputComponent from './MultiLanguageInputComponent.vue';

export default {
    components: { MultiLanguageInputComponent },
    props: {
        item: { type: Object, default: () => ({}) },
        index: { type: Number, required: true },
        selectedLanguage: { type: String, default: 'uk' },
        availableLanguages: { type: Array, default: () => ['uk', 'ru'] },
        errors: { type: Object, default: () => ({}) },
        isFirst: Boolean,
        isLast: Boolean,
    },
    emits: ['delete', 'move-up', 'move-down'],
};
</script>

<template>
    <article class="card border mb-3">
        <div class="card-header d-flex align-items-center justify-content-between bg-light">
            <strong>{{ $t('admin.home_step') }} {{ index + 1 }}</strong>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary" :disabled="isFirst" @click="$emit('move-up')">↑</button>
                <button type="button" class="btn btn-outline-secondary" :disabled="isLast" @click="$emit('move-down')">↓</button>
                <button type="button" class="btn btn-outline-danger" @click="$emit('delete')">{{ $t('admin.delete') }}</button>
            </div>
        </div>
        <div class="card-body">
            <input-component
                :title="$t('admin.home_step_number')"
                :name="`content_sections[steps][items][${index}][number]`"
                :model-value="item.number || ''"
                :is-required="false"
                :errors="errors"
            />
            <multi-language-input-component
                :title="$t('admin.home_item_title')"
                :name="`content_sections[steps][items][${index}][title]`"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="true"
                :init-data="item.title || {}"
                :errors="errors"
            />
            <multi-language-text-area-component
                :title="$t('admin.home_item_text')"
                :name="`content_sections[steps][items][${index}][text]`"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="false"
                :init-data="item.text || {}"
                :errors="errors"
            />
        </div>
    </article>
</template>
