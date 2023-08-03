@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                @isset($blogArticle)
                    <h2 class="page-title">{{ trans('admin.blog_article_edit') }}</h2>
                @else
                    <h2 class="page-title">{{ trans('admin.blog_article_new') }}</h2>
                @endisset
                <blog-article-container
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    submit-route="{{ isset($blogArticle) ?  route('admin.blog-article.edit', ['blogArticle' => $blogArticle->id]) : route('admin.blog-article.create') }}"
                    back-route="{{ route('admin.blog-article.list.page') }}"
                    products-search-route="{{ route('admin.product.list', ['productType' => \App\Models\ProductType::where('slug', config('domain.wallpaper_product_type_slug'))->first()->id]) }}"
                    :available-blocks="{{ json_encode(App\DataClasses\BlogArticleBlockTypesDataClass::get()) }}"

                    :categories="{{ json_encode(\App\Models\BlogCategory::select(['id', 'name'])->get()->map(function($category) { return ['id' => $category->id, 'name' => $category->name]; } )) }}"
                    @if (isset($blogArticle))
                        :selected-category="{{ $blogArticle->blog_category_id }}"
                        article-slug="{{ $blogArticle->slug }}"
                    @endif
                    :article-name="{{ json_encode(isset($blogArticle) ? $blogArticle->getTranslations('name') : []) }}"
                    :article-sub-title="{{ json_encode(isset($blogArticle) ? $blogArticle->getTranslations('sub_title') : []) }}"
                    hero-image="{{ isset($blogArticle) ? $blogArticle->hero_image_url : '' }}"
                    :dynamic-content="{{ json_encode(isset($blogArticle) ? $blogArticle->blocks : []) }}"
                    :meta-title="{{ json_encode(isset($blogArticle) ? $blogArticle->getTranslations('meta_title') : []) }}"
                    :meta-description="{{ json_encode(isset($blogArticle) ? $blogArticle->getTranslations('meta_description') : []) }}"
                    :meta-keywords="{{ json_encode(isset($blogArticle) ? $blogArticle->getTranslations('meta_keywords') : []) }}"
                />
            </div>
        </div>
    </div>
@endsection
@section('vue')
    <vue/>
@endsection

