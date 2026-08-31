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
            <strong>{{ $t('admin.home_metric') }} {{ index + 1 }}</strong>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary" :disabled="isFirst" @click="$emit('move-up')">↑</button>
                <button type="button" class="btn btn-outline-secondary" :disabled="isLast" @click="$emit('move-down')">↓</button>
                <button type="button" class="btn btn-outline-danger" @click="$emit('delete')">{{ $t('admin.delete') }}</button>
            </div>
        </div>
        <div class="card-body">
            <input-component
                :title="$t('admin.home_metric_value')"
                :name="`content_sections[numbers][items][${index}][value]`"
                :model-value="item.value || ''"
                :is-required="true"
                :errors="errors"
            />
            <multi-language-input-component
                :title="$t('admin.home_metric_label')"
                :name="`content_sections[numbers][items][${index}][label]`"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="true"
                :init-data="item.label || {}"
                :errors="errors"
            />
        </div>
    </article>
</template>
