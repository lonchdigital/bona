@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="mb-2 page-title">{{ $productType->name }}</h2>
                <p class="card-text">{{ trans('admin.products_description', ['PRODUCT_TYPE' => $productType->name]) }}</p>
                <div class="row">
                    <div class="col-md-12 d-flex justify-content-between">
                        <a href="{{ route('admin.product.create.page', ['productType' => $productType->id]) }}" class="btn mb-2 btn-dark">{{ trans('admin.product_create') }}</a>
                        <x-admin.custom-dropdown>
                            <x-slot name="button">
                                {{ trans('admin.search') }}
                            </x-slot>
                            <x-slot name="dropdown">
                                <form class="row p-3" action="{{ route('admin.product.list.page', ['productType' => $productType->id]) }}" method="GET">
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label for="search">{{ trans('admin.search') }}</label>
                                            <input type="text" id="search" name="search" class="form-control" placeholder="{{ trans('admin.filter_by_name') }}" value="{{ $searchData->search }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="brand_id">{{ trans('admin.brand') }}</label>
                                            <select class="form-control select2" name="brand_id" id="brand_id">
                                                @if(!$searchData->brandId)
                                                    <option value="" selected hidden>{{ trans('admin.select_brand') }}</option>
                                                @endif
                                                @foreach($brands as $brand)
                                                    <option value="{{ $brand->id }}" @if($searchData->brandId === $brand->id) selected @endif>{{ $brand->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="color_id">{{ trans('admin.color') }}</label>
                                            <select class="form-control select2" name="color_id" id="color_id">
                                                @if(!$searchData->colorId)
                                                    <option value="" selected hidden>{{ trans('admin.select_color') }}</option>
                                                @endif
                                                @foreach($colors as $color)
                                                    <option value="{{ $color->id }}" data-value="{{ $color->hex }}" @if($searchData->colorId === $color->id) selected @endif>{{ $color->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="collection_id">{{ trans('admin.collection') }}</label>
                                            <select class="form-control select2-multi" name="collection_id" id="collection_id">
                                                @if(!$searchData->collectionId)
                                                    <option value="" selected hidden>{{ trans('admin.select_collection') }}</option>
                                                @endif
                                                @foreach($collections as $collection)
                                                    <option value="{{ $collection->id }}" @if($searchData->collectionId === $collection->id) selected @endif>{{ $collection->brand->name }}, {{ $collection->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="country_id">{{ trans('admin.country') }}</label>
                                            <select class="form-control" name="country_id" id="country_id">
                                                @if(!$searchData->countryId)
                                                    <option value="" selected hidden>{{ trans('admin.select_country') }}</option>
                                                @endif
                                                @foreach($countries as $country)
                                                    <option value="{{ $country->id }}" @if($searchData->countryId === $country->id) selected @endif data-image="{{ $country->image_url }}">{{ $country->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <button type="submit" class="btn btn-dark w-100">{{ trans('admin.search') }}</button>
                                    </div>
                                    @if($searchData->search || $searchData->brandId || $searchData->colorId || $searchData->collectionId || $searchData->countryId)
                                        <div class="col-md-12">
                                            <a href="{{ route('admin.product.list.page', ['productType' => $productType->id]) }}" class="btn btn-dark w-100">{{ trans('admin.clear') }}</a>
                                        </div>
                                    @endif
                                </form>
                            </x-slot>
                        </x-admin.custom-dropdown>
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
                                <div id="dataTable-1_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <!-- table start -->
                                            <table class="table datatables" id="dataTable-1">
                                                <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>#</th>
                                                    <th>{{ trans('admin.sku') }}</th>
                                                    <th>{{ trans('admin.name') }}</th>
                                                    <th>{{ trans('admin.color') }}</th>
                                                    <th>{{ trans('admin.brand') }}</th>
                                                    <th>{{ trans('admin.collection') }}</th>
                                                    <th>{{ trans('admin.author') }}</th>
                                                    <th class="text-center">{{ trans('admin.created_at') }}</th>
                                                    <th class="text-right">{{ trans('admin.action') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($productsPaginated as $product)
                                                    <tr>
                                                        <td>
                                                            <a href="#{{$product->id}}" class="text-decoration-none pt-2 expand-button">
                                                                <i class="expand-icon fe fe-chevron-right fe-24"></i>
                                                            </a>
                                                        </td>
                                                        <td>{{ $product->id }}</td>
                                                        <td><strong>{{ $product->sku }}</strong></td>
                                                        <td><strong>{{ $product->name }}</strong></td>
                                                        <td><span><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><rect width="20" height="20" rx="3" fill="{{ $product->color->hex }}"></rect></svg></span> {{ $product->color->name }}</td>
                                                        <td>{{ $product->brand->name }}</td>
                                                        <td>{{ $product->collection->name }}</td>
                                                        <td>{{ $product->creator->first_name }} {{ $product->creator->last_name }}</td>
                                                        <td class="text-center">{{ $product->created_at->format('d-m-Y') }}</td>
                                                        <td class="text-right">
                                                            <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="{{ route('admin.product.edit.page', ['productType' => $productType->id, 'product' => $product->id]) }}">{{ trans('admin.edit') }}</a>
                                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteProductModal-{{ $product->id }}">{{ trans('admin.delete') }}</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @foreach($product->children as $childProduct)
                                                        <tr class="bg-light parrent-{{$product->id}} d-none">
                                                            <td></td>
                                                            <td>{{ $childProduct->id }}</td>
                                                            <td><strong>{{ $childProduct->sku }}</strong></td>
                                                            <td><strong>{{ $childProduct->name }}</strong></td>
                                                            <td><span><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><rect width="20" height="20" rx="3" fill="{{ $childProduct->color->hex }}"></rect></svg></span> {{ $childProduct->color->name }}</td>
                                                            <td>{{ $childProduct->brand->name }}</td>
                                                            <td>{{ $childProduct->collection->name }}</td>
                                                            <td>{{ $childProduct->creator->first_name }} {{ $childProduct->creator->last_name }}</td>
                                                            <td class="text-center">{{ $childProduct->created_at->format('d-m-Y') }}</td>
                                                            <td class="text-right">
                                                                <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-right">
                                                                    <a class="dropdown-item" href="{{ route('admin.product.edit.page', ['productType' => $productType->id, 'product' => $childProduct->id]) }}">{{ trans('admin.edit') }}</a>
                                                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteProductModal-{{ $childProduct->id }}">{{ trans('admin.delete') }}</a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                                </tbody>
                                            </table>
                                            <!-- table end -->
                                        </div>
                                    </div>
                                    {{ $productsPaginated->links('pagination.admin') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @foreach($productsPaginated as $product)
        <div class="modal fade" id="deleteProductModal-{{ $product->id }}" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="defaultModalLabel">{{ trans('admin.product_delete') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">{{ trans('admin.product_delete_confirm_text', ['PRODUCT_NAME' => $product->name]) }}</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('admin.close') }}</button>
                        <form action="{{ route('admin.product.delete', ['productType' => $product->product_type_id, 'product' => $product->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger">{{ trans('admin.delete') }}</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
        @foreach($product->children as $childProduct)
            <div class="modal fade" id="deleteProductModal-{{ $childProduct->id }}" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="defaultModalLabel">{{ trans('admin.product_delete') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">{{ trans('admin.product_delete_confirm_text', ['PRODUCT_NAME' => $childProduct->name]) }}</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('admin.close') }}</button>
                            <form action="{{ route('admin.product.delete', ['productType' => $childProduct->product_type_id, 'product' => $childProduct->id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger">{{ trans('admin.delete') }}</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach
@endsection
@push('scripts')
    <script src="/static-admin/js/jquery-helpers.js"></script>
    <script>
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

        $('.expand-button').click(function () {
            const parentId = $(this).attr('href').substring(1);
            const childElements = $('.parrent-' + parentId);

            if (!$(this).hasClass('active')) {
                $(this).addClass('active');
                $(this).find('.expand-icon').removeClass('fe-chevron-right');
                $(this).find('.expand-icon').addClass('fe-chevron-down');
                childElements.removeClass('d-none');
            } else {
                $(this).removeClass('active');
                $(this).find('.expand-icon').addClass('fe-chevron-right');
                $(this).find('.expand-icon').removeClass('fe-chevron-down');
                childElements.addClass('d-none');
            }
        });
    </script>
@endpush
