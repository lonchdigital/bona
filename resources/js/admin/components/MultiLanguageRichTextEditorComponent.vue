<script>
import { QuillEditor } from "@vueup/vue-quill";
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const defaultToolbarOptions = {
    modules: {
        toolbar: {
            container: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'header': [1, 2, 3, 4, 5, 6] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['blockquote', 'code-block'],
                ['clean'],
            ],
        },
    },
};

export default {
    components: {
        QuillEditor,
    },
    props: {
        options: {
            type: Object,
            default: null,
        },
        selectedLanguage: String,
        availableLanguages: {
            type: Array,
            default: () => [],
        },
        initData: {
            type: [Object, Array],
            default: () => ({}),
        },
        title: String,
        isRequired: {
            type: Boolean,
            default: false,
        },
        name: String,
        content: {
            type: [Object, Array],
            default: () => ({}),
        },
        errors: {
            type: Object,
            default: () => ({}),
        },
        allowHtmlSource: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            editorContent: {},
            sourceModeByLanguage: {},
        };
    },
    computed: {
        editorOptions() {
            return this.options || defaultToolbarOptions;
        },
        errorsToDisplay() {
            const errors = [];

            for (const [key, value] of Object.entries(this.errors)) {
                const name = this.name.replaceAll('[', '.').replaceAll(']', '');
                this.availableLanguages.forEach((availableLanguage) => {
                    if (key.includes(name + '.' + availableLanguage)) {
                        errors.push(value);
                    }
                });
            }

            return errors;
        },
    },
    created() {
        this.syncInitialContent();
    },
    watch: {
        content: {
            deep: true,
            handler() {
                this.syncInitialContent();
            },
        },
        initData: {
            deep: true,
            handler() {
                this.syncInitialContent();
            },
        },
        availableLanguages() {
            this.syncInitialContent();
        },
    },
    methods: {
        syncInitialContent() {
            const content = this.hasLanguageValues(this.content)
                ? this.content
                : this.initData;
            const nextContent = {};

            this.availableLanguages.forEach((language) => {
                nextContent[language] = typeof content?.[language] === 'string'
                    ? content[language]
                    : '';
            });

            this.editorContent = nextContent;

            this.availableLanguages.forEach((language) => {
                if (! Object.prototype.hasOwnProperty.call(this.sourceModeByLanguage, language)) {
                    this.sourceModeByLanguage[language] = false;
                }
            });
        },
        hasLanguageValues(value) {
            return value !== null
                && typeof value === 'object'
                && this.availableLanguages.some((language) => Object.prototype.hasOwnProperty.call(value, language));
        },
        updateContent(language, value) {
            this.editorContent[language] = typeof value === 'string' ? value : '';
        },
        isSourceMode(language) {
            return this.sourceModeByLanguage[language] === true;
        },
        toggleSourceMode(language) {
            this.sourceModeByLanguage[language] = ! this.isSourceMode(language);
        },
        ready(quill) {
            quill.clipboard.addMatcher(Node.ELEMENT_NODE, (node, delta) => {
                const ops = [];

                delta.ops.forEach((op) => {
                    if (op.insert && typeof op.insert === 'string') {
                        ops.push({ insert: op.insert });
                    }
                });

                delta.ops = ops;

                return delta;
            });
        },
    },
};
</script>

<template>
    <div class="row mb-3">
        <div class="col-md-12">
            <div v-for="availableLanguage in availableLanguages" :key="availableLanguage" class="tab-content">
                <div class="multilang-content tab-pane fade" :class="{'active show': availableLanguage === selectedLanguage}">
                    <div class="form-group mb-1">
                        <div class="rich-text-editor__heading">
                            <label :for="name + '-' + availableLanguage">
                                {{ title }}
                                <strong>{{ availableLanguage.toUpperCase() }}</strong>
                                <strong v-if="isRequired" class="text-danger">*</strong>
                            </label>
                            <button
                                v-if="allowHtmlSource"
                                type="button"
                                class="btn btn-sm rich-text-editor__source-toggle"
                                :class="{'is-active': isSourceMode(availableLanguage)}"
                                :aria-pressed="isSourceMode(availableLanguage)"
                                :title="isSourceMode(availableLanguage) ? $t('admin.blog_article_show_visual_editor') : $t('admin.blog_article_show_html')"
                                @click="toggleSourceMode(availableLanguage)"
                            >
                                <span class="rich-text-editor__source-icon" aria-hidden="true">&lt;/&gt;</span>
                                <span>
                                    {{ isSourceMode(availableLanguage)
                                        ? $t('admin.blog_article_show_visual_editor')
                                        : $t('admin.blog_article_show_html') }}
                                </span>
                            </button>
                        </div>
                        <input
                            :id="name + '-' + availableLanguage"
                            type="hidden"
                            :name="name + '[' + availableLanguage + ']'"
                            :value="editorContent[availableLanguage] || ''"
                        >
                        <textarea
                            v-if="isSourceMode(availableLanguage)"
                            class="form-control rich-text-editor__source"
                            :value="editorContent[availableLanguage] || ''"
                            :aria-label="$t('admin.blog_article_html_source') + ' ' + availableLanguage.toUpperCase()"
                            rows="18"
                            spellcheck="false"
                            @input="updateContent(availableLanguage, $event.target.value)"
                        ></textarea>
                        <QuillEditor
                            v-else
                            :key="name + '-' + availableLanguage + '-visual'"
                            theme="snow"
                            content-type="html"
                            :content="editorContent[availableLanguage] || ''"
                            :options="editorOptions"
                            @update:content="updateContent(availableLanguage, $event)"
                            @ready="ready"
                        />
                    </div>
                </div>
            </div>
            <div class="mt-1 text-danger">
                <template v-for="errorsByField in errorsToDisplay">
                    <p v-for="error in errorsByField">{{ error }}</p>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
.rich-text-editor__heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.rich-text-editor__heading label {
    margin-bottom: 0;
}

.rich-text-editor__source-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    min-height: 34px;
    padding: 0.35rem 0.7rem;
    color: #5f6368;
    background: #fff;
    border: 1px solid #d8dce1;
    border-radius: 0.35rem;
    white-space: nowrap;
}

.rich-text-editor__source-toggle:hover,
.rich-text-editor__source-toggle:focus-visible,
.rich-text-editor__source-toggle.is-active {
    color: #212529;
    background: #f3f4f6;
    border-color: #9da3aa;
}

.rich-text-editor__source-toggle:focus-visible {
    outline: 2px solid rgba(66, 133, 244, 0.35);
    outline-offset: 2px;
}

.rich-text-editor__source-icon {
    font-family: SFMono-Regular, Consolas, "Liberation Mono", monospace;
    font-size: 0.78rem;
    font-weight: 700;
    line-height: 1;
}

.rich-text-editor__source {
    min-height: 360px;
    padding: 1rem;
    color: #202124;
    background: #fbfcfd;
    border-color: #cfd4da;
    font-family: SFMono-Regular, Consolas, "Liberation Mono", monospace;
    font-size: 0.875rem;
    line-height: 1.6;
    resize: vertical;
    tab-size: 2;
    white-space: pre;
    overflow: auto;
}

.rich-text-editor__source:focus {
    background: #fff;
    border-color: #8b929a;
    box-shadow: 0 0 0 0.2rem rgba(33, 37, 41, 0.08);
}

@media (max-width: 575.98px) {
    .rich-text-editor__heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .rich-text-editor__source-toggle {
        width: 100%;
    }

    .rich-text-editor__source {
        min-height: 280px;
    }
}
</style>
