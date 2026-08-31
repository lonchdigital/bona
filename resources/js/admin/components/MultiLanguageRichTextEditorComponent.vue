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
    },
    data() {
        return {
            editorContent: {},
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
        },
        hasLanguageValues(value) {
            return value !== null
                && typeof value === 'object'
                && this.availableLanguages.some((language) => Object.prototype.hasOwnProperty.call(value, language));
        },
        updateContent(language, value) {
            this.editorContent[language] = typeof value === 'string' ? value : '';
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
                        <label :for="name + '-' + availableLanguage">
                            {{ title }}
                            <strong>{{ availableLanguage.toUpperCase() }}</strong>
                            <strong v-if="isRequired" class="text-danger">*</strong>
                        </label>
                        <input
                            :id="name + '-' + availableLanguage"
                            type="hidden"
                            :name="name + '[' + availableLanguage + ']'"
                            :value="editorContent[availableLanguage] || ''"
                        >
                        <QuillEditor
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
