<script>
import MultiLanguageInputComponent from './MultiLanguageInputComponent.vue';

export default {
    components: { MultiLanguageInputComponent },
    props: {
        item: { type: Object, default: () => ({}) },
        index: { type: Number, required: true },
        section: { type: String, required: true },
        selectedLanguage: { type: String, default: 'uk' },
        availableLanguages: { type: Array, default: () => ['uk', 'ru'] },
        errors: { type: Object, default: () => ({}) },
        showUrl: { type: Boolean, default: false },
        isFirst: Boolean,
        isLast: Boolean,
    },
    emits: ['delete', 'move-up', 'move-down'],
};
</script>

<template>
    <article class="card border mb-3">
        <div class="card-header d-flex align-items-center justify-content-between bg-light">
            <strong>{{ $t(section === 'ideas' ? 'admin.home_idea' : 'admin.home_work') }} {{ index + 1 }}</strong>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary" :disabled="isFirst" @click="$emit('move-up')">↑</button>
                <button type="button" class="btn btn-outline-secondary" :disabled="isLast" @click="$emit('move-down')">↓</button>
                <button type="button" class="btn btn-outline-danger" @click="$emit('delete')">{{ $t('admin.delete') }}</button>
            </div>
        </div>
        <div class="card-body">
            <input type="hidden" :name="`content_sections[${section}][items][${index}][existing_image_path]`" :value="item.image_path || ''">
            <input type="hidden" :name="`content_sections[${section}][items][${index}][default_image]`" :value="item.default_image || ''">

            <multi-language-input-component
                :title="$t('admin.home_item_title')"
                :name="`content_sections[${section}][items][${index}][title]`"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="true"
                :init-data="item.title || {}"
                :errors="errors"
            />
            <multi-language-text-area-component
                :title="$t('admin.home_item_text')"
                :name="`content_sections[${section}][items][${index}][text]`"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="false"
                :init-data="item.text || {}"
                :errors="errors"
            />
            <input-component
                v-if="showUrl"
                :title="$t('admin.home_item_url')"
                :name="`content_sections[${section}][items][${index}][url]`"
                :model-value="item.url || ''"
                :is-required="false"
                :errors="errors"
            />
            <image-file-input-component
                :title="$t('admin.home_item_image')"
                :name="`content_sections[${section}][items][${index}][image]`"
                :image-deleted-name="`content_sections[${section}][items][${index}][image_deleted]`"
                :is-required="!item.image_url"
                :errors="errors"
                :init-data="item.image_url || null"
            />
        </div>
    </article>
</template>
