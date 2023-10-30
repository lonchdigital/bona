@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="mb-2 page-title">{{ trans('admin.product_fields') }}</h2>
                <p class="card-text">{{ trans('admin.product_fields_description') }}</p>
                <div class="row">
                    <div class="col-md-12">
                        <a href="{{ route('admin.product-attribute.create.page') }}"
                           class="btn mb-2 btn-dark">{{ trans('admin.product_field_create') }}</a>
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
                                                    <th>{{ trans('admin.name') }}</th>
                                                    <th>{{ trans('admin.created_at') }}</th>
                                                    <th class="text-right">{{ trans('admin.action') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($productAttributesPaginated as $productAttribute)
                                                    <tr>
                                                        <td>{{ $productAttribute->id }}</td>
                                                        <td class="w-25 text-left">{{ $productAttribute->attribute_name }}</td>
                                                        <td>{{ $productAttribute->created_at->format('d-m-Y') }}</td>
                                                        <td class="text-right">
                                                            <button class="btn btn-sm dropdown-toggle more-horizontal"
                                                                    type="button" data-toggle="dropdown"
                                                                    aria-haspopup="true" aria-expanded="false">
                                                                <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item"
                                                                   href="{{ route('admin.product-attribute.edit.page', ['productAttribute' => $productAttribute->id]) }}">{{ trans('admin.edit') }}</a>
                                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                                   data-target="#deleteProductFieldModal-{{ $productAttribute->id }}">{{ trans('admin.delete') }}</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                            <!-- table end -->
                                        </div>
                                    </div>
                                    {{ $productAttributesPaginated->links('pagination.admin') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @foreach($productAttributesPaginated as $productAttribute)
        <div class="modal fade" id="deleteProductFieldModal-{{ $productAttribute->id }}" tabindex="-1" role="dialog"
             aria-labelledby="defaultModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="defaultModalLabel">{{ trans('admin.field_delete') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">{{ trans('admin.product_field_delete_confirm_text', ['FIELD_NAME' => $productAttribute->attribute_name]) }}</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ trans('admin.close') }}</button>
                        <form action="{{ route('admin.product-attribute.delete', ['productAttribute' => $productAttribute->id]) }}"
                              method="POST">
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
    </script>
@endpush
