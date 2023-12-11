@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="mb-2 page-title">{{ trans('admin.products_import_list_title', ['PRODUCT_TYPE' => $productType->name]) }}</h2>
                <p class="card-text">{{ trans('admin.products_import_list_description', ['PRODUCT_TYPE' => $productType->name]) }}</p>
                <div class="row">
                    <div class="col d-flex justify-content-between">
                        <div class="row">
                            <div class="col d-flex">
                                <form class="mx-1" action="{{ route('admin.products-import.delete-products', ['productType' => $productType->id]) }}" method="post">
                                    @csrf
                                    <button class="btn btn-dark" type="submit">{{ trans('admin.products_import_delete_products') }}</button>
                                </form>
                                <form class="mx-1" action="{{ route('admin.products-import.save-products', ['productType' => $productType->id]) }}" method="post">
                                    @csrf
                                    <button class="btn btn-dark" type="submit">{{ trans('admin.products_import_save_products') }}</button>
                                </form>
                            </div>
                        </div>
                        <x-admin.custom-dropdown>
                            <x-slot name="button">
                                {{ trans('admin.search') }}
                            </x-slot>
                            <x-slot name="dropdown">
                                <div class="wide-filter-dropdown">
                                    <form class="row p-3" action="{{ route('admin.products-import.list', ['productType' => $productType->id]) }}" method="GET">
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="search">{{ trans('admin.search') }}</label>
                                                <input type="text" id="search" name="search" class="form-control" placeholder="{{ trans('admin.filter_by_name') }}" value="{{ $searchData->search }}">
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label for="brand_id">{{ trans('admin.brand') }}</label>
                                                    <select class="form-control select2" name="brand_id" id="brand_id">
                                                        <option value="" @if(!$searchData->brandId) selected @endif>{{ trans('admin.all') }}</option>
                                                        @foreach($brands as $brand)
                                                            <option value="{{ $brand->id }}" @if($searchData->brandId === $brand->id) selected @endif>{{ $brand->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="color_id">{{ trans('admin.color') }}</label>
                                                    <select class="form-control select2" name="color_id" id="color_id">
                                                        <option value="" @if(!$searchData->colorId) selected @endif>{{ trans('admin.all') }}</option>
                                                        @foreach($colors as $color)
                                                            <option value="{{ $color->id }}" data-value="{{ $color->hex }}" @if($searchData->colorId === $color->id) selected @endif>{{ $color->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-row">

                                                <div class="form-group col-md-6">
                                                    <label for="country_id">{{ trans('admin.country') }}</label>
                                                    <select class="form-control" name="country_id" id="country_id">
                                                        <option value="" @if(!$searchData->countryId) selected @endif>{{ trans('admin.all') }}</option>
                                                        @foreach($countries as $country)
                                                            <option value="{{ $country->id }}" @if($searchData->countryId === $country->id) selected @endif data-image="{{ $country->image_url }}">{{ $country->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label for="main_image">{{ trans('admin.product_main_image') }}</label>
                                                    <select class="form-control" name="main_image" id="main_image">
                                                        <option value="" @if(!$searchData->collectionId) selected @endif>{{ trans('admin.all') }}</option>
                                                        @foreach(\App\DataClasses\ProductImportFilterImagesStatusesDataClass::get() as $imageStatus)
                                                            <option value="{{ $imageStatus['id'] }}" @if($searchData->mainImageStatusId == $imageStatus['id']) selected @endif>{{ $imageStatus['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="pattern_image">{{ trans('admin.product_image_pattern') }}</label>
                                                    <select class="form-control" name="pattern_image" id="pattern_image">
                                                        <option value="" @if(!$searchData->collectionId) selected @endif>{{ trans('admin.all') }}</option>
                                                        @foreach(\App\DataClasses\ProductImportFilterImagesStatusesDataClass::get() as $imageStatus)
                                                            <option value="{{ $imageStatus['id'] }}" @if($searchData->patternImageStatusId == $imageStatus['id']) selected @endif>{{ $imageStatus['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label for="gallery_image_1">{{ trans('admin.product_gallery_image_1') }}</label>
                                                    <select class="form-control" name="gallery_image_1" id="gallery_image_1">
                                                        <option value="" @if(!$searchData->collectionId) selected @endif>{{ trans('admin.all') }}</option>
                                                        @foreach(\App\DataClasses\ProductImportFilterImagesStatusesDataClass::get() as $imageStatus)
                                                            {{ $searchData->galleryImage1StatusId }} {{ $imageStatus['id'] }}
                                                            <option value="{{ $imageStatus['id'] }}" @if($searchData->galleryImage1StatusId == $imageStatus['id']) selected @endif>{{ $imageStatus['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="gallery_image_2">{{ trans('admin.product_gallery_image_2') }}</label>
                                                    <select class="form-control" name="gallery_image_2" id="gallery_image_2">
                                                        <option value="" @if(!$searchData->collectionId) selected @endif>{{ trans('admin.all') }}</option>
                                                        @foreach(\App\DataClasses\ProductImportFilterImagesStatusesDataClass::get() as $imageStatus)
                                                            <option value="{{ $imageStatus['id'] }}" @if($searchData->galleryImage2StatusId == $imageStatus['id']) selected @endif>{{ $imageStatus['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label for="gallery_image_3">{{ trans('admin.product_gallery_image_3') }}</label>
                                                    <select class="form-control" name="gallery_image_3" id="gallery_image_3">
                                                        <option value="" @if(!$searchData->collectionId) selected @endif>{{ trans('admin.all') }}</option>
                                                        @foreach(\App\DataClasses\ProductImportFilterImagesStatusesDataClass::get() as $imageStatus)
                                                            <option value="{{ $imageStatus['id'] }}" @if($searchData->galleryImage3StatusId == $imageStatus['id']) selected @endif>{{ $imageStatus['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="gallery_image_4">{{ trans('admin.product_gallery_image_4') }}</label>
                                                    <select class="form-control" name="gallery_image_4" id="gallery_image_4">
                                                        <option value="" @if(!$searchData->collectionId) selected @endif>{{ trans('admin.all') }}</option>
                                                        @foreach(\App\DataClasses\ProductImportFilterImagesStatusesDataClass::get() as $imageStatus)
                                                            <option value="{{ $imageStatus['id'] }}" @if($searchData->galleryImage4StatusId == $imageStatus['id']) selected @endif>{{ $imageStatus['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="gallery_image_5">{{ trans('admin.product_gallery_image_5') }}</label>
                                                <select class="form-control" name="gallery_image_5" id="gallery_image_5">
                                                    <option value="" @if(!$searchData->collectionId) selected @endif>{{ trans('admin.all') }}</option>
                                                    @foreach(\App\DataClasses\ProductImportFilterImagesStatusesDataClass::get() as $imageStatus)
                                                        <option value="{{ $imageStatus['id'] }}" @if($searchData->galleryImage5StatusId == $imageStatus['id']) selected @endif>{{ $imageStatus['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="custom-control custom-checkbox mb-2">
                                                <input type="hidden" name="show_new" value="0">
                                                <input class="custom-control-input" value="1" type="checkbox" id="show_new" name="show_new" @if($searchData->showNew) checked="" @endif>
                                                <label class="custom-control-label" for="show_new">{{ trans('admin.products_import_show_new') }}</label>
                                            </div>

                                            <div class="custom-control custom-checkbox mb-2">
                                                <input type="hidden" name="show_existing" value="0">
                                                <input class="custom-control-input" value="1" type="checkbox" id="show_existing" name="show_existing" @if($searchData->showExisting) checked="" @endif>
                                                <label class="custom-control-label" for="show_existing">{{ trans('admin.products_import_show_existing') }}</label>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <button type="submit" class="btn btn-dark w-100">{{ trans('admin.search') }}</button>
                                        </div>
                                        @if($searchData->search || $searchData->brandId || $searchData->colorId || $searchData->collectionId || $searchData->countryId)
                                            <div class="col-md-12">
                                                <a href="{{ route('admin.products-import.list', ['productType' => $productType->id]) }}" class="btn btn-dark w-100">{{ trans('admin.clear') }}</a>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            </x-slot>
                        </x-admin.custom-dropdown>
                    </div>
                </div>
                <div class="row my-4">
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
                                <div class="row justify-content-end pb-3">
                                    <div class="col-sm-6 col-md-4 col-lg-2">

                                    </div>
                                </div>
                                <table class="table table-hover overflow-auto d-block">
                                    <thead>
                                    <tr>
                                        <th>{{ trans('admin.parent_sku') }}</th>
                                        <th>{{ trans('admin.sku') }}</th>
                                        <th>{{ trans('admin.name') }}</th>
                                        <th>{{ trans('admin.product_main_image') }}</th>
                                        <th>{{ trans('admin.product_image_pattern') }}</th>
                                        <th>{{ trans('admin.product_gallery_image_1') }}</th>
                                        <th>{{ trans('admin.product_gallery_image_2') }}</th>
                                        <th>{{ trans('admin.product_gallery_image_3') }}</th>
                                        <th>{{ trans('admin.product_gallery_image_4') }}</th>
                                        <th>{{ trans('admin.product_gallery_image_5') }}</th>
                                        <th>{{ trans('admin.status') }}</th>
                                        <th>{{ trans('admin.action') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($importedProductsPaginated as $product)
                                        <tr>
                                            <td>{{ $product->parent->sku ?? '' }}</td>
                                            <td>{{ $product->sku }}</td>
                                            <td>{{ $product->name }}</td>
                                            <td>
                                                <form action="{{ route('admin.products-import.upload-image', ['importedProduct' => $product->id]) }}" class="dropzone dropzone-image" id="product-main-image-{{ $product->id }}">
                                                    @csrf
                                                    <input type="hidden" name="existing_image_url" value="{{ $product->main_image_url }}">
                                                    <input type="hidden" name="type_id" value="{{ \App\DataClasses\ImportedProductImageTypesDataClass::TYPE_MAIN_IMAGE }}">
                                                    <input type="hidden" name="imported_product_id" value="{{ $product->id }}">
                                                    <div class="dz-default dz-message"><button class="dz-button" type="button">{{ trans('admin.products_import_upload_image') }}</button></div>
                                                </form>
                                            </td>
                                            <td>
                                                <form action="{{ route('admin.products-import.upload-image', ['importedProduct' => $product->id]) }}" class="dropzone dropzone-image" id="product-pattern-image-{{ $product->id }}">
                                                    @csrf
                                                    <input type="hidden" name="existing_image_url" value="{{ $product->pattern_image_url }}">
                                                    <input type="hidden" name="type_id" value="{{ \App\DataClasses\ImportedProductImageTypesDataClass::TYPE_PATTERN_IMAGE }}">
                                                    <input type="hidden" name="imported_product_id" value="{{ $product->id }}">
                                                    <div class="dz-default dz-message"><button class="dz-button" type="button">{{ trans('admin.products_import_upload_image') }}</button></div>
                                                </form>
                                            </td>
                                            <td>
                                                <form action="{{ route('admin.products-import.upload-image', ['importedProduct' => $product->id]) }}" class="dropzone dropzone-image" id="product-gallery-1-image-{{ $product->id }}">
                                                    @csrf
                                                    <input type="hidden" name="existing_image_url" value="{{ $product->gallery_image1_url }}">
                                                    <input type="hidden" name="type_id" value="{{ \App\DataClasses\ImportedProductImageTypesDataClass::TYPE_GALLERY_IMAGE_1 }}">
                                                    <input type="hidden" name="imported_product_id" value="{{ $product->id }}">
                                                    <div class="dz-default dz-message"><button class="dz-button" type="button">{{ trans('admin.products_import_upload_image') }}</button></div>
                                                </form>
                                            </td>
                                            <td>
                                                <form action="{{ route('admin.products-import.upload-image', ['importedProduct' => $product->id]) }}" class="dropzone dropzone-image" id="product-gallery-2-image-{{ $product->id }}">
                                                    @csrf
                                                    <input type="hidden" name="existing_image_url" value="{{ $product->gallery_image2_url }}">
                                                    <input type="hidden" name="type_id" value="{{ \App\DataClasses\ImportedProductImageTypesDataClass::TYPE_GALLERY_IMAGE_2 }}">
                                                    <input type="hidden" name="imported_product_id" value="{{ $product->id }}">
                                                    <div class="dz-default dz-message"><button class="dz-button" type="button">{{ trans('admin.products_import_upload_image') }}</button></div>
                                                </form>
                                            </td>
                                            <td>
                                                <form action="{{ route('admin.products-import.upload-image', ['importedProduct' => $product->id]) }}" class="dropzone dropzone-image" id="product-gallery-3-image-{{ $product->id }}">
                                                    @csrf
                                                    <input type="hidden" name="existing_image_url" value="{{ $product->gallery_image3_url }}">
                                                    <input type="hidden" name="type_id" value="{{ \App\DataClasses\ImportedProductImageTypesDataClass::TYPE_GALLERY_IMAGE_3 }}">
                                                    <input type="hidden" name="imported_product_id" value="{{ $product->id }}">
                                                    <div class="dz-default dz-message"><button class="dz-button" type="button">{{ trans('admin.products_import_upload_image') }}</button></div>
                                                </form>
                                            </td>
                                            <td>
                                                <form action="{{ route('admin.products-import.upload-image', ['importedProduct' => $product->id]) }}" class="dropzone dropzone-image" id="product-gallery-4-image-{{ $product->id }}">
                                                    @csrf
                                                    <input type="hidden" name="existing_image_url" value="{{ $product->gallery_image4_url }}">
                                                    <input type="hidden" name="type_id" value="{{ \App\DataClasses\ImportedProductImageTypesDataClass::TYPE_GALLERY_IMAGE_4 }}">
                                                    <input type="hidden" name="imported_product_id" value="{{ $product->id }}">
                                                    <div class="dz-default dz-message"><button class="dz-button" type="button">{{ trans('admin.products_import_upload_image') }}</button></div>
                                                </form>
                                            </td>
                                            <td>
                                                <form action="{{ route('admin.products-import.upload-image', ['importedProduct' => $product->id]) }}" class="dropzone dropzone-image" id="product-gallery-5-image-{{ $product->id }}">
                                                    @csrf
                                                    <input type="hidden" name="existing_image_url" value="{{ $product->gallery_image5_url }}">
                                                    <input type="hidden" name="type_id" value="{{ \App\DataClasses\ImportedProductImageTypesDataClass::TYPE_GALLERY_IMAGE_5 }}">
                                                    <input type="hidden" name="imported_product_id" value="{{ $product->id }}">
                                                    <div class="dz-default dz-message"><button class="dz-button" type="button">{{ trans('admin.products_import_upload_image') }}</button></div>
                                                </form>
                                            </td>
                                            @if($product->is_existing_product)
                                                <td><span class="badge badge-pill badge-success">{{ trans('admin.products_import_is_existing') }}</span></td>
                                            @else
                                                <td><span class="badge badge-pill badge-primary">{{ trans('admin.products_import_is_new') }}</span></td>
                                            @endif
                                            <td class="text-right">
                                                <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" href="{{ route('admin.products-import.details', ['importedProduct' => $product->id]) }}">{{ trans('admin.products_import_see_details') }}</a>
                                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteProductModal-{{ $product->id }}">{{ trans('admin.delete') }}</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                {{ $importedProductsPaginated->links('pagination.admin') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @foreach($importedProductsPaginated as $product)
        <div class="modal fade" id="deleteProductModal-{{ $product->id }}" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="defaultModalLabel">{{ trans('admin.product_delete') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">{{ trans('admin.product_import_delete_confirm_text', ['PRODUCT_NAME' => $product->name]) }}</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('admin.close') }}</button>
                        <form action="{{ route('admin.products-import.delete', ['importedProduct' => $product->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger">{{ trans('admin.delete') }}</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
@push('scripts')
    <script src="/static-admin/js/jquery-helpers.js"></script>
    <script>
        // This prevents Dropzone to autodiscover the elements,
        // allowing you to better control it.
        Dropzone.autoDiscover = false;

        $('.dropzone-image').each(function () {
            const dropzone = new Dropzone($(this)[0], {
                addRemoveLinks: true,
                maxFiles: 1,
                dictRemoveFile: '{{ trans('admin.products_import_remove_image') }}',
                dictCancelUpload: '{{ trans('admin.products_import_cancel_upload_image') }}',
                dictCancelUploadConfirmation: '{{ trans('admin.products_import_cancel_upload_image_confirmation') }}',
            });

            const productId = $(this).find('input[name="imported_product_id"]').val();
            const typeId = $(this).find('input[name="type_id"]').val();
            const existingImageUrl = $(this).find('input[name="existing_image_url"]').val();
            const csrf = $(this).find('input[name="_token"]').val();

            dropzone.on('removedfile', function () {
                $.ajax({
                    url: '{{ route('admin.products-import.remove-image', ['importedProduct' => 'IMPORTED_PRODUCT_ID']) }}'.replace('IMPORTED_PRODUCT_ID', productId),
                    type: 'post',
                    data: {
                        _token: csrf,
                       type_id: typeId,
                    },
                    dataType: 'json',
                }).fail(function (data) {

                });
            });

            if (existingImageUrl !== '') {
                let mockFile = { name: "image", size: 0 };
                dropzone.displayExistingFile(mockFile, existingImageUrl);

            }
        });

        $('#country_id').select2({
            templateResult: formatStateCountry,
            templateSelection: formatStateCountry,
            theme: 'bootstrap4',
        });

        $('#color_id').select2({
            templateResult: formatStateColor,
            templateSelection: formatStateColor,
            theme: 'bootstrap4',
        });

        $('#brand_id').select2({
            theme: 'bootstrap4',
        });

        $('#collection_id').select2({
            theme: 'bootstrap4',
        });

    </script>
@endpush
