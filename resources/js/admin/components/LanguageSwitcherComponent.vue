<script>
export default {
    props: {
        availableLanguages: Array,
        modelValue: String,
    },
    emits: [
        'update:modelValue'
    ],
    data() {
        return {
            selectedLanguage: this.modelValue,
        }
    },
    methods: {
        selectLanguage(language) {
            this.selectedLanguage = language;
            this.$emit('update:modelValue', language)
        }
    }
}
</script>

<template>
    <ul class="nav nav-pills nav-fill">
        <li class="nav-item d-flex flex-grow-0" v-for="availableLanguage in availableLanguages" :key="availableLanguage">
            <a class="multilang-switch nav-link py-1 px-2" :class="{ 'active': availableLanguage === selectedLanguage }" href="#" @click.prevent="selectLanguage(availableLanguage)">
                {{ availableLanguage.toUpperCase() }}
            </a>
        </li>
    </ul>
</template>
