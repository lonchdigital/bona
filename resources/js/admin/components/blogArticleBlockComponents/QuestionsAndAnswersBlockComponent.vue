<script>
export default {
    props: {
        name: {
            type: String,
            default: '',
        },
        content: {
            type: Object,
            default: {},
        },
        blockId: {
            type: Number,
            default: null,
        },
        selectedLanguage: String,
        availableLanguages: Array,
        errors: {
            type: Object,
            default: {},
        }
    },
    data() {
        return {
            questions: {
                type: Array,
                default: [],
            },
            quillOptions: {
                modules: {
                    toolbar: ['bold', 'italic', 'underline', 'strike'],
                }
            },
        };
    },
    beforeMount() {
        if (this.content.hasOwnProperty('questions') && this.content.questions.length) {
            this.questions = this.content.questions;
        } else {
            this.questions = [{}]
        }
    },
    emits: [
        'deleteBlock'
    ],
    methods: {
        deleteBlock() {
            this.$emit('deleteBlock');
        },
        addQuestion() {
            this.questions.push({});
        },
        deleteQuestion(index) {
            this.questions.splice(index, 1);
        }
    }
}
</script>

<template>
    <input type="hidden" :name="name + '[type_id]'" value="7">
    <input type="hidden" :name="name + '[id]'" :value="blockId">
    <div class="row blog-article-block mb-3">
        <div class="col mt-2">
            <div class="row">
                <div class="col">
                    <p>
                        <strong>
                            {{ $t('admin.blog_article_block_questions_and_answers') }}
                        </strong>
                    </p>
                </div>
            </div>
            <div class="row" v-for="(question, index) in questions">
                <div class="col mx-2">
                    <div class="row">
                        <div class="col">
                            <multi-language-rich-text-editor-component
                                :options="quillOptions"
                                :name="name + '[questions][' + index + '][question]'"
                                :selected-language="selectedLanguage"
                                :available-languages="availableLanguages"
                                :title="$t('admin.blog_article_block_question')"
                                :content="question.hasOwnProperty('question') ? question.question : []"
                                :errors="errors"
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <multi-language-rich-text-editor-component
                                :options="quillOptions"
                                :name="name + '[questions][' + index + '][answer]'"
                                :selected-language="selectedLanguage"
                                :available-languages="availableLanguages"
                                :title="$t('admin.blog_article_block_answer')"
                                :content="question.hasOwnProperty('answer') ? question.answer : []"
                                :errors="errors"
                            />
                        </div>
                    </div>
                    <div class="row" v-if="index >= 1">
                        <div class="col">
                            <a href="#" class="btn mb-5 btn-danger" @click.prevent="deleteQuestion(index)">
                                {{ $t('admin.delete_question') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <a href="#" class="btn mb-2 btn-dark" @click.prevent="addQuestion">
                        <span class="fe fe-plus-square fe-16 mr-2"></span>
                        {{ $t('admin.add_question') }}
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <a href="#" class="btn mb-2 btn-danger" @click.prevent="deleteBlock">
                        <span class="fe fe-trash-2 fe-16 mr-2"></span>
                        {{ $t('admin.delete_block') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
