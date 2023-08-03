@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">{{ trans('admin.seogen') }}</h2>
                <seogen-edit-form
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    :product-types="{{ json_encode($productTypes) }}"
                    submit-route="{{ route('admin.seo-gen.edit') }}"
                    :seogen-categories="{{ json_encode($seogenCategories) }}"
                    :seogen-products="{{ json_encode($seogenProducts) }}"
                    :seogen-brand="{{ json_encode($seogenBrand) }}"
                    @if(Session::has('success'))
                        success=" {{ Session::get('success') }}"
                    @endif
                    @if(Session::has('error'))
                        success=" {{ Session::get('error') }}"
                    @endif
                />
            </div>
        </div>
    </div>
@endsection
@section('vue')
    <vue/>
@endsection
