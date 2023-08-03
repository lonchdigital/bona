@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="mb-2 page-title">{{ trans('admin.products_import_title', ['PRODUCT_TYPE' => $productType->name]) }}</h2>
                <p class="card-text">{{ trans('admin.products_import_description', ['PRODUCT_TYPE' => $productType->name]) }}</p>
                <div class="row">
                    <div class="col-md-12">
                        <a href="{{ route('admin.products-import.download-example', ['productType' => $productType->id]) }}" class="btn mb-2 btn-dark">{{ trans('admin.products_import_download_example') }}</a>
                    </div>
                </div>
                <div class="row my-4">
                    <!-- Small table -->
                    <div class="col-md-12">
                        <div class="card shadow">
                            <div class="card-body">
                                @if(Session::has('success'))
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-success" role="alert">
                                                {{ Session::get('success') }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if(Session::has('error'))
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-danger" role="alert">
                                                {{ Session::get('error') }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <x-admin.reactive-form method="POST" action="{{ route('admin.products-import.upload', ['productType' => $productType]) }}">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label for="customFile">{{ trans('admin.products_import_file_for_import') }} <strong class="text-danger">*</strong> ({{ trans('admin.products_import_file_for_import_explanation') }})</label>
                                        <div class="custom-file">
                                            <input type="file" name="file" class="custom-file-input" id="file">
                                            <label class="custom-file-label" for="file">{{ trans('admin.choose_file') }}</label>
                                            <div class="mt-1 text-danger ajaxError" id="error-field-file"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 text-right">
                                            <button type="submit" class="btn btn-dark">{{ trans('admin.products_import_start_import') }}</button>
                                        </div>
                                    </div>
                                </x-admin.reactive-form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')

@endpush
