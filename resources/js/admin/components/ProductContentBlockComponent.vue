<script>
import MultiLanguageInputComponent from './MultiLanguageInputComponent.vue';
import MultiLanguageRichTextEditorComponent from './MultiLanguageRichTextEditorComponent.vue';
import ImageFileInputComponent from './ImageFileInputComponent.vue';

export default {
    components: {
        MultiLanguageInputComponent,
        MultiLanguageRichTextEditorComponent,
        ImageFileInputComponent,
    },
    props: {
        block: { type: Object, default: () => ({}) },
        index: { type: Number, required: true },
        selectedLanguage: { type: String, default: 'uk' },
        availableLanguages: { type: Array, default: () => ['uk', 'ru'] },
        errors: { type: Object, default: () => ({}) },
        isFirst: { type: Boolean, default: false },
        isLast: { type: Boolean, default: false },
    },
    emits: ['delete', 'move'],
    data() {
        return {
            featureItems: Array.isArray(this.block.items) ? this.block.items : [],
        };
    },
    computed: {
        fieldPrefix() {
            return `content_blocks[${this.index}]`;
        },
        blockTitle() {
            const labels = {
                text: this.$t('admin.product_block_text'),
                image_text: this.$t('admin.product_block_image_text'),
                features: this.$t('admin.product_block_features'),
                benefits: this.$t('admin.product_block_benefits'),
                full_kit: this.$t('admin.product_block_full_kit'),
                journey: this.$t('admin.product_block_journey'),
                installments: this.$t('admin.product_block_installments'),
                quote: this.$t('admin.product_block_quote'),
            };

            return labels[this.block.type] || this.$t('admin.product_content_block');
        },
    },
    methods: {
        addFeature() {
            this.featureItems.push({});
        },
        deleteFeature(index) {
            this.featureItems.splice(index, 1);
        },
    },
};
</script>

<template>
    <section class="card mb-4 border product-content-block">
        <input type="hidden" :name="fieldPrefix + '[id]'" :value="block.id">
        <input type="hidden" :name="fieldPrefix + '[type]'" :value="block.type">

        <div class="card-header d-flex flex-wrap align-items-center justify-content-between" style="gap: .75rem">
            <strong>{{ index + 1 }}. {{ blockTitle }}</strong>
            <div class="d-flex" style="gap: .4rem">
                <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="isFirst" @click="$emit('move', -1)" :aria-label="$t('admin.move_up')">↑</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="isLast" @click="$emit('move', 1)" :aria-label="$t('admin.move_down')">↓</button>
                <button type="button" class="btn btn-sm btn-danger" @click="$emit('delete')">
                    <span class="fe fe-trash-2 fe-16 mr-1"></span>{{ $t('admin.delete_block') }}
                </button>
            </div>
        </div>

        <div class="card-body">
            <multi-language-input-component
                :title="$t('admin.product_block_eyebrow')"
                :name="fieldPrefix + '[eyebrow]'"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="false"
                :init-data="block.eyebrow || {}"
                :errors="errors"
            />

            <multi-language-input-component
                :title="$t('admin.title')"
                :name="fieldPrefix + '[title]'"
                :selected-language="selectedLanguage"
                :available-languages="availableLanguages"
                :is-required="false"
                :init-data="block.title || {}"
                :errors="errors"
            />

            <template v-if="['text', 'image_text', 'full_kit', 'journey', 'installments'].includes(block.type)">
                <multi-language-rich-text-editor-component
                    :title="$t('admin.description')"
                    :name="fieldPrefix + '[content]'"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :content="block.content || {}"
                    :errors="errors"
                />
            </template>

            <template v-if="block.type === 'image_text'">
                <image-file-input-component
                    :title="$t('admin.image')"
                    :name="fieldPrefix + '[image]'"
                    :image-deleted-name="fieldPrefix + '[image_deleted]'"
                    :is-required="false"
                    :init-data="block.image_url || null"
                    :errors="errors"
                />

                <div class="form-group mb-3">
                    <label :for="fieldPrefix + '-image-position'">{{ $t('admin.product_block_image_position') }}</label>
                    <select class="form-control" :id="fieldPrefix + '-image-position'" :name="fieldPrefix + '[image_position]'">
                        <option value="left" :selected="(block.image_position || 'left') === 'left'">{{ $t('admin.left') }}</option>
                        <option value="right" :selected="block.image_position === 'right'">{{ $t('admin.right') }}</option>
                    </select>
                </div>

                <multi-language-input-component
                    :title="$t('admin.text_button')"
                    :name="fieldPrefix + '[button_label]'"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="block.button_label || {}"
                    :errors="errors"
                />

                <div class="form-group mb-3">
                    <label :for="fieldPrefix + '-button-url'">{{ $t('admin.button_link') }}</label>
                    <input class="form-control" :id="fieldPrefix + '-button-url'" :name="fieldPrefix + '[button_url]'" :value="block.button_url || ''">
                </div>
            </template>

            <template v-if="['features', 'benefits', 'full_kit', 'journey'].includes(block.type)">
                <div class="border rounded p-3 mb-3" v-for="(item, itemIndex) in featureItems" :key="itemIndex">
                    <multi-language-input-component
                        :title="$t('admin.title')"
                        :name="fieldPrefix + '[items][' + itemIndex + '][title]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="false"
                        :init-data="item.title || {}"
                        :errors="errors"
                    />
                    <multi-language-input-component
                        v-if="block.type === 'journey'"
                        :title="$t('admin.product_block_meta')"
                        :name="fieldPrefix + '[items][' + itemIndex + '][meta]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="false"
                        :init-data="item.meta || {}"
                        :errors="errors"
                    />
                    <multi-language-input-component
                        :title="$t('admin.description')"
                        :name="fieldPrefix + '[items][' + itemIndex + '][text]'"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :is-required="false"
                        :init-data="item.text || {}"
                        :errors="errors"
                    />
                    <button type="button" class="btn btn-sm btn-outline-danger" @click="deleteFeature(itemIndex)">{{ $t('admin.delete') }}</button>
                </div>
                <button type="button" class="btn btn-secondary" @click="addFeature">
                    <span class="fe fe-plus-square fe-16 mr-2"></span>{{ $t('admin.product_block_add_feature') }}
                </button>
            </template>

            <template v-if="block.type === 'quote'">
                <multi-language-rich-text-editor-component
                    :title="$t('admin.product_block_quote')"
                    :name="fieldPrefix + '[quote]'"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :content="block.quote || {}"
                    :errors="errors"
                />
                <multi-language-input-component
                    :title="$t('admin.blog_article_block_quote_author')"
                    :name="fieldPrefix + '[author]'"
                    :selected-language="selectedLanguage"
                    :available-languages="availableLanguages"
                    :is-required="false"
                    :init-data="block.author || {}"
                    :errors="errors"
                />
            </template>
        </div>
    </section>
</template>
