import "bootstrap";

import { createApp } from 'vue/dist/vue.esm-bundler';
import { i18nVue } from "laravel-vue-i18n";

import LanguageSwitcherComponent from "./components/LanguageSwitcherComponent.vue";
import BlogArticleContainer from "./containers/BlogArticleContainer.vue";
import BlogArticleBlocksContainer from "./containers/BlogArticleBlocksContainer.vue";
import MultiLanguageInputComponent from "./components/MultiLanguageInputComponent.vue";
import DateInputComponent from "./components/DateInputComponent.vue";
import MultiLanguageTextAreaComponent from "./components/MultiLanguageTextAreaComponent.vue";
import TextAreaComponent from "./components/TextAreaComponent.vue";
import MultiLanguageRichTextEditorComponent from "./components/MultiLanguageRichTextEditorComponent.vue";
import ImageFileInputComponent from "./components/ImageFileInputComponent.vue";
import ImageWithTooltipBlockComponent from "./components/blogArticleBlockComponents/ImageWithTooltipBlockComponent.vue";
import MultiLanguageRichTextEditorBlockComponent from "./components/blogArticleBlockComponents/MultiLanguageRichTextEditorBlockComponent.vue";
import ImageWithTooltipBlockContainerComponent from "./components/blogArticleBlockComponents/ImageWithTooltipBlockContainerComponent.vue";
import QuoteBlockComponent from "./components/blogArticleBlockComponents/QuoteBlockComponent.vue";
import SponsorBlockComponent from "./components/blogArticleBlockComponents/SponsorBlockComponent.vue";
import VideoBlockComponent from "./components/blogArticleBlockComponents/VideoBlockComponent.vue";
import SliderBlockComponent from "./components/blogArticleBlockComponents/SliderBlockComponent.vue";
import QuestionsAndAnswersBlockComponent from "./components/blogArticleBlockComponents/QuestionsAndAnswersBlockComponent.vue";
import InputComponent from "./components/InputComponent.vue";
import CheckBoxComponent from "./components/CheckBoxComponent.vue";
import SelectComponent from "./components/SelectComponent.vue";
import SelectColorComponent from "./components/SelectColorComponent.vue";
import ReactiveFormContainer from "./containers/ReactiveFormContainer.vue";
import BlogSlidesEditForm from "./forms/BlogSlidesEditForm.vue";
import BlogSlideComponent from "./components/BlogSlideComponent.vue";
import HomePageEditForm from "./forms/HomePageEditForm.vue";
import ProductPageEditForm from "./forms/ProductPageEditForm.vue";
import WorkPageEditForm from "./forms/WorkPageEditForm.vue";
import ServicesPageEditForm from "./forms/ServicesPageEditForm.vue";
import CommonSectionPageEditForm from "./forms/CommonSectionPageEditForm.vue";
import ContactPageEditForm from "./forms/ContactPageEditForm.vue";
import ApplicationConfigsPageEditForm from "./forms/ApplicationConfigsPageEditForm.vue";
import SeogenEditFrom from "./forms/SeogenEditForm.vue";
import FilterGroupsEditForm from "./forms/FilterGroupsEditForm.vue";

import Multiselect from '@vueform/multiselect';

const app = createApp({});

app.use(i18nVue, {
    fallbackLang: 'uk',
    resolve: async lang => {
        try {
            const langs = import.meta.glob('../../../lang/*.json');
            return await langs[`../../../lang/${lang}.json`]();
        } catch (e) {
            //console.error(e);
        }
    }
});

app.component('language-switcher-component', LanguageSwitcherComponent);
app.component('blog-article-container', BlogArticleContainer);
app.component('blog-article-blocks-container', BlogArticleBlocksContainer);
app.component('multi-language-input-component', MultiLanguageInputComponent);
app.component('date-input-component', DateInputComponent);
app.component('multi-language-text-area-component', MultiLanguageTextAreaComponent);
app.component('text-area-component', TextAreaComponent);
app.component('multi-language-rich-text-editor-component', MultiLanguageRichTextEditorComponent);
app.component('image-file-input-component', ImageFileInputComponent);
app.component('image-with-tooltip-block', ImageWithTooltipBlockComponent);
app.component('image-with-tooltip-block-container', ImageWithTooltipBlockContainerComponent);
app.component('multi-language-rich-text-editor-block-component', MultiLanguageRichTextEditorBlockComponent);
app.component('quote-block-component', QuoteBlockComponent);
app.component('sponsor-block-component', SponsorBlockComponent);
app.component('video-block-component', VideoBlockComponent);
app.component('slider-block-component', SliderBlockComponent);
app.component('questions-and-answers-block-component', QuestionsAndAnswersBlockComponent);
app.component('input-component', InputComponent);
app.component('check-box-component', CheckBoxComponent);
app.component('select-component', SelectComponent);
app.component('select-color-component', SelectColorComponent);
app.component('reactive-form-container', ReactiveFormContainer);
app.component('blog-slides-edit-form', BlogSlidesEditForm);
app.component('blog-slide-component', BlogSlideComponent);
app.component('home-page-edit-form', HomePageEditForm);
app.component('product-page-edit-form', ProductPageEditForm);
app.component('work-page-edit-form', WorkPageEditForm);
app.component('services-page-edit-form', ServicesPageEditForm);
app.component('common-section-page-edit-form', CommonSectionPageEditForm);
app.component('contact-page-edit-form', ContactPageEditForm);
app.component('application-configs-page-edit-form', ApplicationConfigsPageEditForm);
app.component('seogen-edit-form', SeogenEditFrom);
app.component('filter-groups-edit-form', FilterGroupsEditForm);

app.component('multiselect-component', Multiselect);

app.mount('#app');

