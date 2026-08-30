import "bootstrap";

import { createApp, defineAsyncComponent } from 'vue/dist/vue.esm-bundler';
import { i18nVue } from "laravel-vue-i18n";

import LanguageSwitcherComponent from "./components/LanguageSwitcherComponent.vue";
import MultiLanguageInputComponent from "./components/MultiLanguageInputComponent.vue";
import DateInputComponent from "./components/DateInputComponent.vue";
import MultiLanguageTextAreaComponent from "./components/MultiLanguageTextAreaComponent.vue";
import TextAreaComponent from "./components/TextAreaComponent.vue";
import MultiLanguageRichTextEditorComponent from "./components/MultiLanguageRichTextEditorComponent.vue";
import ImageFileInputComponent from "./components/ImageFileInputComponent.vue";
import InputComponent from "./components/InputComponent.vue";
import CheckBoxComponent from "./components/CheckBoxComponent.vue";
import SelectComponent from "./components/SelectComponent.vue";
import SelectColorComponent from "./components/SelectColorComponent.vue";

import Multiselect from '@vueform/multiselect';

const app = createApp({});
const asyncComponent = loader => defineAsyncComponent(loader);

const BlogArticleContainer = asyncComponent(() => import('./containers/BlogArticleContainer.vue'));
const BlogArticleBlocksContainer = asyncComponent(() => import('./containers/BlogArticleBlocksContainer.vue'));
const ImageWithTooltipBlockComponent = asyncComponent(() => import('./components/blogArticleBlockComponents/ImageWithTooltipBlockComponent.vue'));
const MultiLanguageRichTextEditorBlockComponent = asyncComponent(() => import('./components/blogArticleBlockComponents/MultiLanguageRichTextEditorBlockComponent.vue'));
const ImageWithTooltipBlockContainerComponent = asyncComponent(() => import('./components/blogArticleBlockComponents/ImageWithTooltipBlockContainerComponent.vue'));
const QuoteBlockComponent = asyncComponent(() => import('./components/blogArticleBlockComponents/QuoteBlockComponent.vue'));
const SponsorBlockComponent = asyncComponent(() => import('./components/blogArticleBlockComponents/SponsorBlockComponent.vue'));
const VideoBlockComponent = asyncComponent(() => import('./components/blogArticleBlockComponents/VideoBlockComponent.vue'));
const SliderBlockComponent = asyncComponent(() => import('./components/blogArticleBlockComponents/SliderBlockComponent.vue'));
const QuestionsAndAnswersBlockComponent = asyncComponent(() => import('./components/blogArticleBlockComponents/QuestionsAndAnswersBlockComponent.vue'));
const ReactiveFormContainer = asyncComponent(() => import('./containers/ReactiveFormContainer.vue'));
const BlogSlidesEditForm = asyncComponent(() => import('./forms/BlogSlidesEditForm.vue'));
const BlogSlideComponent = asyncComponent(() => import('./components/BlogSlideComponent.vue'));
const HomePageEditForm = asyncComponent(() => import('./forms/HomePageEditForm.vue'));
const ProductPageEditForm = asyncComponent(() => import('./forms/ProductPageEditForm.vue'));
const AuthorPageEditForm = asyncComponent(() => import('./forms/AuthorPageEditForm.vue'));
const AuthorCertificateComponent = asyncComponent(() => import('./components/AuthorCertificateComponent.vue'));
const WorkImageComponent = asyncComponent(() => import('./components/WorkImageComponent.vue'));
const AboutUsFactComponent = asyncComponent(() => import('./components/AboutUsFactComponent.vue'));
const AboutUsStepComponent = asyncComponent(() => import('./components/AboutUsStepComponent.vue'));
const AboutUsTeamMemberComponent = asyncComponent(() => import('./components/AboutUsTeamMemberComponent.vue'));
const WorkPageEditForm = asyncComponent(() => import('./forms/WorkPageEditForm.vue'));
const ServicesPageEditForm = asyncComponent(() => import('./forms/ServicesPageEditForm.vue'));
const CommonSectionPageEditForm = asyncComponent(() => import('./forms/CommonSectionPageEditForm.vue'));
const AboutUsPageEditForm = asyncComponent(() => import('./forms/AboutUsPageEditForm.vue'));
const ContactPageEditForm = asyncComponent(() => import('./forms/ContactPageEditForm.vue'));
const ApplicationConfigsPageEditForm = asyncComponent(() => import('./forms/ApplicationConfigsPageEditForm.vue'));
const SeogenEditFrom = asyncComponent(() => import('./forms/SeogenEditForm.vue'));
const FilterGroupsEditForm = asyncComponent(() => import('./forms/FilterGroupsEditForm.vue'));

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
app.component('author-page-edit-form', AuthorPageEditForm);
app.component('author-certificate-component', AuthorCertificateComponent);
app.component('work-image-component', WorkImageComponent);
app.component('about-us-fact-component', AboutUsFactComponent);
app.component('about-us-step-component', AboutUsStepComponent);
app.component('about-us-team-member-component', AboutUsTeamMemberComponent);
app.component('work-page-edit-form', WorkPageEditForm);
app.component('services-page-edit-form', ServicesPageEditForm);
app.component('common-section-page-edit-form', CommonSectionPageEditForm);
app.component('about-us-page-edit-form', AboutUsPageEditForm);
app.component('contact-page-edit-form', ContactPageEditForm);
app.component('application-configs-page-edit-form', ApplicationConfigsPageEditForm);
app.component('seogen-edit-form', SeogenEditFrom);
app.component('filter-groups-edit-form', FilterGroupsEditForm);

app.component('multiselect-component', Multiselect);

app.mount('#app');
