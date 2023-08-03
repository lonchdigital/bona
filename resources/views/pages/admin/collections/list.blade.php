@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="mb-2 page-title">{{ trans('admin.collections') }}</h2>
                <p class="card-text">{{ trans('admin.collections_description') }}</p>
                <div class="row">
                    <div class="col-md-12 d-flex justify-content-between">
                        <a href="{{ route('admin.collection.create.page') }}" class="btn mb-2 btn-dark">{{ trans('admin.collection_add') }}</a>
                        <x-admin.custom-dropdown>
                            <x-slot name="button">
                                {{ trans('admin.search') }}
                            </x-slot>
                            <x-slot name="dropdown">
                                <form class="row p-3" action="{{ route('admin.collection.list.page') }}" method="GET">
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label for="search">{{ trans('admin.search') }}</label>
                                            <input type="text" id="search" name="search" class="form-control" placeholder="{{ trans('admin.filter_by_name') }}" value="{{ $search }}">
                                        </div>
                                        <div class="form-group" id="test">
                                            <label for="brand_id">{{ trans('admin.brand') }}</label>
                                            <select multiple="multiple" class="form-control select2-multi" name="brand_id[]" id="brand_id">
                                                @foreach($brands as $brand)
                                                    <option value="{{ $brand->id }}" @if($searchBrandIds && in_array($brand->id, $searchBrandIds)) selected @endif>{{ $brand->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <button type="submit" class="btn btn-dark w-100">{{ trans('admin.search') }}</button>
                                    </div>
                                    @if(isset($search) || isset($searchBrandIds))
                                        <div class="col-md-12">
                                            <a href="{{ route('admin.collection.list.page') }}" class="btn btn-dark w-100">{{ trans('admin.clear') }}</a>
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
                                                    <th>#</th>
                                                    <th class="w-25">{{ trans('admin.name') }}</th>
                                                    <th class="w-25">{{ trans('admin.brand') }}</th>
                                                    <th class="text-center">{{ trans('admin.author') }}</th>
                                                    <th class="text-center">{{ trans('admin.created_at') }}</th>
                                                    <th class="text-right">{{ trans('admin.action') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($collectionsPaginated as $collection)
                                                    <tr>
                                                        <td>{{ $collection->id }}</td>
                                                        <td>{{ $collection->name }}</td>
                                                        <td>{{ $collection->brand->name }}</td>
                                                        <td class="text-center">{{ $collection->creator->first_name }} {{ $collection->creator->last_name }}</td>
                                                        <td class="text-center">{{ $collection->created_at->format('d-m-Y') }}</td>
                                                        <td class="text-right">
                                                            <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="{{ route('admin.collection.edit.page', ['collection' => $collection->id]) }}">{{ trans('admin.edit') }}</a>
                                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteProductFieldModal-{{ $collection->id }}">{{ trans('admin.delete') }}</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                            <!-- table end -->
                                        </div>
                                    </div>
                                    {{ $collectionsPaginated->links('pagination.admin') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @foreach($collectionsPaginated as $collection)
        <div class="modal fade" id="deleteProductFieldModal-{{ $collection->id }}" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="defaultModalLabel">{{ trans('admin.collection_delete') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">{{ trans('admin.collection_delete_confirm_text', ['COLLECTION_NAME' => $collection->name]) }}</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('admin.close') }}</button>
                        <form action="{{ route('admin.collection.delete', ['collection' => $collection->id]) }}" method="POST">
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
    <script type='text/javascript'>
        $(document).ready(function() {
            $('.select2-multi').select2(
                {
                    multiple: true,
                    allowClear: true,
                    placeholder: '{{ trans('admin.filter_by_brand') }}',
                    theme: 'bootstrap4',
                });
        });
    </script>
@endpush
