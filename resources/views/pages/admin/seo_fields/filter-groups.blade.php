@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">{{ trans('admin.filter_groups') }}</h2>
                <filter-groups-edit-form
                    @isset($filterGroup)
                        submit-route="{{ route('admin.filter-groups.edit', ['filterGroup' => $filterGroup]) }}"
                        back-route="{{ route('admin.filter-groups.list.page') }}"
                        :name="{{ json_encode($filterGroup->getTranslations('name')) }}"
                        slug="{{ $filterGroup->slug }}"
                        :title-tag="{{ json_encode($filterGroup->getTranslations('title_tag')) }}"
                        :meta-title="{{ json_encode($filterGroup->getTranslations('meta_title')) }}"
                        :meta-description="{{ json_encode($filterGroup->getTranslations('meta_description')) }}"
                        :meta-keywords="{{ json_encode($filterGroup->getTranslations('meta_keywords')) }}"
                        :product-type-id="{{ $filterGroup->product_type_id }}"
                        :filters="{{ json_encode($filterGroup->filters) }}"
                    @else
                        submit-route="{{ route('admin.filter-groups.create') }}"
                    @endisset
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    :brands="{{ json_encode($brands) }}"
                    :countries="{{ json_encode($countries) }}"
                    :colors="{{ json_encode($colors) }}"
                    :product-types="{{ json_encode($productTypes) }}"
                />
            </div>
        </div>
    </div>
@endsection
@section('vue')
    <vue/>
@endsection
