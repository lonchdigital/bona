<script>
import { trans } from "laravel-vue-i18n";

export default {
    props: {
        selectedLanguage: String,
        availableBlocks: Array,
        availableLanguages: Array,
        productsSearchRoute: String,
        dynamicContent: Array,
        errors: {
            type: Object,
            default: {},
        }
    },
    beforeMount() {
        this.blocksList = this.dynamicContent;
    },
    data() {
        return {
            blockToCreate: null,
            blockToCreateError: '',
            blocksList: [],
        }
    },
    methods: {
        moveBlock(index, direction) {
            const targetIndex = index + direction;

            if (targetIndex < 0 || targetIndex >= this.blocksList.length) {
                return;
            }

            const [block] = this.blocksList.splice(index, 1);
            this.blocksList.splice(targetIndex, 0, block);
        },
        addBlock() {
            this.blockToCreateError = '';
            if (this.blockToCreate === 1) {
                this.blocksList.push({
                    type_id: 1,
                });
            } else if (this.blockToCreate === 2) {
                this.blocksList.push({
                    type_id: 2,
                });
            } else if (this.blockToCreate === 3) {
                this.blocksList.push({
                    type_id: 3,
                });
            } else if (this.blockToCreate === 4) {
                this.blocksList.push({
                    type_id: 4,
                });
            } else if (this.blockToCreate === 5) {
                this.blocksList.push({
                    type_id: 5,
                });
            } else if (this.blockToCreate === 6) {
                this.blocksList.push({
                    type_id: 6,
                });
            } else if (this.blockToCreate === 7) {
                this.blocksList.push({
                    type_id: 7,
                });
            } else {
                this.blockToCreateError = trans('admin.blog_article_block_required');
            }

        },

        deleteBlockByIndex(index) {
            this.blocksList.splice(index, 1);
        },
    },
}
</script>

<template>
    <div class="row">
        <div class="col">
            <div class="row" v-for="(block, index) in blocksList" :key="block.id || 'new-' + index">
                <div class="col">
                    <div class="d-flex justify-content-end mb-2" style="gap: .4rem">
                        <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="index === 0" @click="moveBlock(index, -1)" :aria-label="$t('admin.move_up')">↑</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="index === blocksList.length - 1" @click="moveBlock(index, 1)" :aria-label="$t('admin.move_down')">↓</button>
                    </div>
                    <multi-language-rich-text-editor-block-component
                        :name="'block[' + index + ']'"
                        v-if="block.type_id === 1"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :title="$t('admin.blog_article_block_text')"
                        :content="block.content"
                        :block-id="block.hasOwnProperty('id') ? block.id : null"
                        :errors="errors"
                        @delete-block="() => deleteBlockByIndex(index)"
                    />
                    <image-with-tooltip-block-container
                        :name="'block[' + index + ']'"
                        v-if="block.type_id === 2"
                        :products-search-route="productsSearchRoute"
                        :content="block.content"
                        :block-id="block.hasOwnProperty('id') ? block.id : null"
                        :errors="errors"
                        @delete-block="() => deleteBlockByIndex(index)"
                    />
                    <quote-block-component
                        :name="'block[' + index + ']'"
                        v-if="block.type_id === 3"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :content="block.content"
                        :block-id="block.hasOwnProperty('id') ? block.id : null"
                        :errors="errors"
                        @delete-block="() => deleteBlockByIndex(index)"
                    />
                    <sponsor-block-component
                        :name="'block[' + index + ']'"
                        v-if="block.type_id === 4"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        :content="block.content"
                        :block-id="block.hasOwnProperty('id') ? block.id : null"
                        :errors="errors"
                        @delete-block="() => deleteBlockByIndex(index)"
                    />
                    <video-block-component
                        :name="'block[' + index + ']'"
                        v-if="block.type_id === 5"
                        :content="block.content"
                        :block-id="block.hasOwnProperty('id') ? block.id : null"
                        :errors="errors"
                        @delete-block="() => deleteBlockByIndex(index)"
                    />
                    <slider-block-component
                        :name="'block[' + index + ']'"
                        :products-search-route="productsSearchRoute"
                        v-if="block.type_id === 6"
                        :content="block.content"
                        :block-id="block.hasOwnProperty('id') ? block.id : null"
                        :errors="errors"
                        @delete-block="() => deleteBlockByIndex(index)"
                    />
                    <questions-and-answers-block-component
                        :name="'block[' + index + ']'"
                        v-if="block.type_id === 7"
                        :content="block.content"
                        :block-id="block.hasOwnProperty('id') ? block.id : null"
                        :errors="errors"
                        :selected-language="selectedLanguage"
                        :available-languages="availableLanguages"
                        @delete-block="() => deleteBlockByIndex(index)"
                    />
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="form-group mb-3">
                <label for="product_field_type">{{  $t('admin.blog_article_block_type') }}<strong class="text-danger">*</strong></label>
                <select v-model="blockToCreate" class="form-control">
                    <option selected value="">{{ $t('admin.blog_article_select_block_type') }}</option>
                    <option :value="availableBlock.id" v-for="availableBlock in availableBlocks" :key="'available-block-option-' + availableBlock.id">{{ availableBlock.name }}</option>
                </select>
                <div class="mt-1 text-danger" v-if="blockToCreateError">{{ blockToCreateError }}</div>
            </div>
        </div>
    </div>
    <div class="row pt-3">
        <div class="col-md-12 text-center">
            <a href="#" @click.prevent="addBlock" id="add-option" class="btn mb-2 btn-secondary">
                <span class="fe fe-plus-square fe-16 mr-2"></span>
                {{  $t('admin.blog_article_add_block') }}
            </a>
        </div>
    </div>
</template>
