<script>
import MultiLanguageInputComponent from './MultiLanguageInputComponent.vue';

export default {
    components: { MultiLanguageInputComponent },
    props: {
        item: {
            type: Object,
            default: () => ({}),
        },
        index: {
            type: Number,
            required: true,
        },
        selectedLanguage: {
            type: String,
            default: 'uk',
        },
        availableLanguages: {
            type: Array,
            default: () => ['uk', 'ru'],
        },
        errors: {
            type: Object,
            default: () => ({}),
        },
        isFirst: Boolean,
        isLast: Boolean,
    },
    emits: ['delete', 'move-up', 'move-down'],
};
</script>

<template>
    <article class="card border mb-3">
        <div class="card-header d-flex align-items-center justify-content-between bg-light">
            <strong>{{ $t('admin.home_style_item') }} {{ index + 1 }}</strong>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary" :disabled="isFirst" @click="$emit('move-up')" :aria-label="$t('admin.move_up')">↑</button>
                <button type="button" class="btn btn-outline-secondary" :disabled="isLast" @click="$emit('move-down')" :aria-label="$t('admin.move_down')">↓</button>
                <button type="button" class="btn btn-outline-danger" @click="$emit('delete')">{{ $t('admin.delete') }}</button>
            </div>
        </div>
        <div class="card-body">
            <input type="hidden" :name="`style_section[items][${index}][existing_image_path]`" :value="item.image_path || ''">

            <multi-language-input-component
                :title="$t('admin.home_style_name')"
                :name="`style_section[items][${index}][name]`"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="true"
                :init-data="item.name || {}"
                :errors="errors"
            />

            <image-file-input-component
                :title="$t('admin.home_style_image')"
                :name="`style_section[items][${index}][image]`"
                :image-deleted-name="`style_section[items][${index}][image_deleted]`"
                :is-required="!item.image_path"
                :errors="errors"
                :init-data="item.image_url || null"
            />
        </div>
    </article>
</template>
