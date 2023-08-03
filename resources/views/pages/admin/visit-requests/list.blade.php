@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="mb-2 page-title">{{ trans('admin.orders') }}</h2>
                <div class="row">
                    <div class="col d-flex justify-content-end">
                        <x-admin.custom-dropdown>
                            <x-slot name="button">
                                {{ trans('admin.search') }}
                            </x-slot>
                            <x-slot name="dropdown">
                                <form class="row p-3" action="{{ route('admin.visit-request.list.page') }}" method="GET">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="status_id">{{ trans('admin.status') }}</label>
                                            <select class="form-control" name="status_id" id="status_id">
                                                <option value="" @if(!$searchData->statusId) selected @endif>{{ trans('admin.select') }} {{ trans('admin.status')  }}</option>
                                                @foreach(\App\DataClasses\VisitRequestStatusesDataClass::get() as $status)
                                                    <option @if($searchData->statusId == $status['id']) selected @endif value="{{ $status['id'] }}">{{ $status['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <button type="submit" class="btn btn-dark w-100">{{ trans('admin.search') }}</button>
                                    </div>
                                    @if($searchData->statusId)
                                        <div class="col-md-12">
                                            <a href="{{ route('admin.visit-request.list.page') }}" class="btn btn-dark w-100">{{ trans('admin.clear') }}</a>
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
                                                    <th>{{ trans('admin.visit_request_type') }}</th>
                                                    <th>{{ trans('admin.name') }}</th>
                                                    <th>{{ trans('admin.phone') }}</th>
                                                    <th class="text-center">{{ trans('admin.order_status') }}</th>
                                                    <th class="text-right">{{ trans('admin.action') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($visitRequestsPaginated as $visitRequest)
                                                    <tr>
                                                        <td>{{ $visitRequest->id }}</td>
                                                        <td>{{ \App\DataClasses\VisitRequestTypeDataClass::get($visitRequest->visit_type_id)['name'] }}</td>
                                                        <td>{{ $visitRequest->name }}</td>
                                                        <td>{{ $visitRequest->phone }}</td>
                                                        <td class="text-center"><span class="badge " style="background-color: {{ \App\DataClasses\VisitRequestStatusesDataClass::get($visitRequest->status_id)['color'] }};"><strong class="text-dark">{{ \App\DataClasses\VisitRequestStatusesDataClass::get($visitRequest->status_id)['name'] }}</strong></span></td>
                                                        <td class="text-right">
                                                            <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="{{ route('admin.visit-request.details.page', ['visitRequest' => $visitRequest->id]) }}">{{ trans('admin.view') }}</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                            <!-- table end -->
                                        </div>
                                    </div>
                                    {{ $visitRequestsPaginated->links('pagination.admin') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $('#status_id').select2({
            theme: 'bootstrap4',
        });
    </script>
@endpush
