@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="mb-2 page-title">{{ trans('admin.visit_requests_list') }}</h2>
                <div class="row">
                    <div class="col d-flex justify-content-end">

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
                                            <table class="table table-hover datatables" id="dataTable-1">
                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>{{ trans('admin.visit_request_created_at') }}</th>
                                                    <th>{{ trans('admin.visit_request_name') }}</th>
                                                    <th>{{ trans('admin.visit_request_phone') }}</th>
                                                    <th class="text-center">{{ trans('admin.status') }}</th>
                                                    <th class="text-right">{{ trans('admin.action') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @forelse($visitRequestsPaginated as $visitRequest)
                                                    <tr class="visit-request-row"
                                                        data-visit-request-row
                                                        data-href="{{ route('admin.visit-request.details.page', ['visitRequest' => $visitRequest->id]) }}">
                                                        <td>{{ $visitRequest->id }}</td>
                                                        <td class="text-nowrap">{{ $visitRequest->created_at->copy()->timezone('Europe/Kyiv')->format('d.m.Y H:i') }}</td>
                                                        <td>
                                                            <a class="visit-request-row__link text-dark"
                                                               href="{{ route('admin.visit-request.details.page', ['visitRequest' => $visitRequest->id]) }}"
                                                               aria-label="{{ trans('admin.visit_request_open', ['id' => $visitRequest->id]) }}">
                                                                {{ $visitRequest->name }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $visitRequest->phone }}</td>
                                                        <td class="text-center"><span class="badge " style="background-color: {{ \App\DataClasses\VisitRequestStatusesDataClass::get($visitRequest->status_id)['color'] }};"><strong class="text-dark">{{ \App\DataClasses\VisitRequestStatusesDataClass::get($visitRequest->status_id)['name'] }}</strong></span></td>
                                                        <td class="text-right">
                                                            <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteVisitRequestModal-{{ $visitRequest->id }}">{{ trans('admin.delete') }}</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="py-4 text-center text-muted">{{ trans('admin.visit_requests_empty') }}</td>
                                                    </tr>
                                                @endforelse
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

    @foreach($visitRequestsPaginated as $visitRequest)
        <div class="modal fade" id="deleteVisitRequestModal-{{ $visitRequest->id }}" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="defaultModalLabel">{{ trans('admin.delete') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">{{ trans('admin.visit_request_delete_confirm_text', ['VISIT_REQUEST_ID' => $visitRequest->id]) }}</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('admin.close') }}</button>
                        <form action="{{ route('admin.visit-request.delete', ['visitRequest' => $visitRequest->id]) }}" method="POST">
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
    <script>
        $('#status_id').select2({
            theme: 'bootstrap4',
        });

        document.querySelectorAll('[data-visit-request-row]').forEach(function (row) {
            function openRequest() {
                window.location.assign(row.dataset.href);
            }

            row.addEventListener('click', function (event) {
                if (event.target.closest('a, button, form, input, select, textarea, [data-toggle]')) {
                    return;
                }

                if (window.getSelection && window.getSelection().toString()) {
                    return;
                }

                openRequest();
            });
        });
    </script>
@endpush
