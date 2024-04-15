<script>
import { QuillEditor } from "@vueup/vue-quill";
import '@vueup/vue-quill/dist/vue-quill.snow.css';

export default {
    components: {
        QuillEditor
    },
    props: {
        options: {
            type: Object,
            default: null,
        },
        selectedLanguage: String,
        availableLanguages: Array,
        initData: {
            type: Object,
            default: {},
        },
        title: String,
        isRequired: {
            type: Boolean,
            default: false,
        },
        name: String,
        content: {
            type: Object,
            default: {},
        },
        errors: {
            type: Object,
            default: {},
        }
    },
    mounted() {
        Object.keys(this.content).forEach(language => {
            if (this.content.hasOwnProperty(language)) {
                this.putHtmlContent('quill_' + language, this.content[language]);
                this.$refs['quill_' + language + '_input'][0].value = this.content[language];
            }
        });


    },
    computed: {
        errorsToDisplay() {
            let errors = [];

            for (const [key, value] of Object.entries(this.errors)) {
                const name = this.name.replaceAll('[', '.').replaceAll(']', '');
                this.availableLanguages.forEach(function (availableLanguage) {
                    if (key.includes(name + '.' + availableLanguage)) {
                        errors.push(value);
                    }
                });
            }
            return errors;
        }
    },
    methods: {
        holdHTMLContent(refName) {
            //workaround to get html from quill editor
            const innerHtml = this.$refs[refName][0].getElementsByClassName('ql-editor')[0].innerHTML;

            if (innerHtml === '<p></p>') {
                this.$refs[refName + '_input'][0].value = '';
            } else {
                this.$refs[refName + '_input'][0].value = this.$refs[refName][0].getElementsByClassName('ql-editor')[0].innerHTML;
            }
        },
        putHtmlContent(refName, content) {
            //workaround to put html to quill editor
            if (this.$refs.hasOwnProperty(refName)) {
                this.$refs[refName][0].getElementsByClassName('ql-editor')[0].innerHTML = content;
            }
        },
        debounce(func, timeout = 300) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => { func.apply(this, args); }, timeout);
            };
        },
        ready(quill) {
            quill.clipboard.addMatcher(Node.ELEMENT_NODE, (node, delta) => {
                let ops = []
                delta.ops.forEach(op => {
                    if (op.insert && typeof op.insert === 'string') {
                        ops.push({
                            insert: op.insert
                        })
                    }
                })
                delta.ops = ops
                return delta
            })
        }
    }
}
</script>

<template>
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="tab-content" v-for="availableLanguage in availableLanguages">
                <div class="multilang-content tab-pane fade" :class="{'active show': availableLanguage === selectedLanguage}">
                    <div class="form-group mb-1" :ref="'quill_' + availableLanguage">
                        <label :for="name + '-' + availableLanguage">{{ title }}
                            <strong>{{ availableLanguage.toUpperCase() }}</strong>
                            <strong v-if="isRequired" class="text-danger">*</strong>
                        </label>
                        <input :ref="'quill_' + availableLanguage + '_input'" type="hidden" :name="name + '[' + availableLanguage + ']'">
                        <QuillEditor
                            v-if="options"
                            theme="snow"
                            @update:content="() => holdHTMLContent('quill_' + availableLanguage)"
                            :options="{
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
                                            ['table']
                                        ],
                                    }
                                }
                            }"
                            @ready="ready"/>
                        <QuillEditor
                            v-else theme="snow"
                            @update:content="() => holdHTMLContent('quill_' + availableLanguage)"
                            :options="{
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
                                        handlers: {
                                            /*'image': function() {
                                                console.log('111111111');
                                            }*/
                                        }
                                    }
                                }
                            }"
                            @ready="ready"/>
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
