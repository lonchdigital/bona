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
                                <form class="row p-3" action="{{ route('admin.order.list.page') }}" method="GET">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="status_id">{{ trans('admin.status') }}</label>
                                            <select class="form-control" name="status_id" id="status_id">
                                                <option value="" @if(!$searchData->statusId) selected @endif>{{ trans('admin.select') }} {{ trans('admin.status')  }}</option>
                                                @foreach(\App\DataClasses\OrderStatusesDataClass::get() as $status)
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
                                            <a href="{{ route('admin.order.list.page') }}" class="btn btn-dark w-100">{{ trans('admin.clear') }}</a>
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
                                                    <th>{{ trans('admin.order_date') }}</th>
                                                    <th>{{ trans('admin.order_person_full_name') }}</th>
                                                    <th>{{ trans('admin.order_person_phone') }}</th>
                                                    <th>{{ trans('admin.order_summary') }}</th>
                                                    <th>{{ trans('admin.additional') }}</th>
                                                    <th class="text-center">{{ trans('admin.order_payment_status') }}</th>
                                                    <th class="text-center">{{ trans('admin.order_status') }}</th>
                                                    <th class="text-right">{{ trans('admin.action') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($ordersPaginated as $order)
                                                    <tr>
                                                        <td>{{ $order->id }}</td>
                                                        <td>{{ $order->created_at->format('d-m-Y H:i') }}</td>
                                                        <td>{{ $order->user->first_name }} {{ $order->user->last_name }}</td>
                                                        <td>{{ $order->user->phone }}</td>
                                                        <td>{{ $order->summary }} {{ $baseCurrency->name_short }}</td>
                                                        <td>
                                                            @if( $order->payment_type_id === App\DataClasses\PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK && $order->payment_status_id === \App\DataClasses\OrderPaymentStatusesDataClass::STATUS_PAID )
                                                                @if( is_null($order->mono_order_state) )
                                                                    <div><a class="btn btn-dark mb-3" href="#" data-toggle="modal" data-target="#rejectOrderModal-{{ $order->id }}">{{ trans('admin.order_reject') }}</a></div>
                                                                    <div><a class="btn btn-dark" href="#" data-toggle="modal" data-target="#confirmOrderModal-{{ $order->id }}">{{ trans('admin.order_confirm') }}</a></div>
                                                                @elseif( $order->mono_order_state == App\DataClasses\MonoBankOrderStateStatusesDataClass::STATUS_CONFIRMED )
                                                                    <div class="mb-3"><span>{{ trans('admin.order_confirmed_by_store') }}</span></div>
                                                                    <div><a class="btn btn-dark" href="#" data-toggle="modal" data-target="#returnOrderModal-{{ $order->id }}">{{ trans('admin.order_return') }}</a></div>
                                                                @elseif( $order->mono_order_state == App\DataClasses\MonoBankOrderStateStatusesDataClass::STATUS_REJECTED )
                                                                    <div><span>{{ trans('admin.order_rejected') }}</span></div>
                                                                @elseif( $order->mono_order_state == App\DataClasses\MonoBankOrderStateStatusesDataClass::STATUS_RETURNED )
                                                                    <div><span>{{ trans('admin.order_returned') }}</span></div>
                                                                @endif
                                                            @endif
                                                        </td>
                                                        <td class="text-center"><span class="badge " style="background-color: {{ \App\DataClasses\OrderPaymentStatusesDataClass::get($order->payment_status_id)['color'] }};"><strong class="text-dark">{{ \App\DataClasses\OrderPaymentStatusesDataClass::get($order->payment_status_id)['name'] }}</strong></span></td>
                                                        <td class="text-center"><span class="badge " style="background-color: {{ \App\DataClasses\OrderStatusesDataClass::get($order->status_id)['color'] }};"><strong class="text-dark">{{ \App\DataClasses\OrderStatusesDataClass::get($order->status_id)['name'] }}</strong></span></td>
                                                        <td class="text-right">
                                                            <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="{{ route('admin.order.details.page', ['order' => $order->id]) }}">{{ trans('admin.view') }}</a>
                                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteOrderModal-{{ $order->id }}">{{ trans('admin.delete') }}</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                            <!-- table end -->
                                        </div>
                                    </div>
                                    {{ $ordersPaginated->links('pagination.admin') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach($ordersPaginated as $order)

        <div class="modal fade" id="deleteOrderModal-{{ $order->id }}" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="defaultModalLabel">{{ trans('admin.order_delete') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">{{ trans('admin.order_delete_confirm_text', ['ORDER_ID' => $order->id]) }}</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('admin.close') }}</button>
                        <form action="{{ route('admin.order.delete', ['order' => $order->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger">{{ trans('admin.delete') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if( $order->payment_type_id === App\DataClasses\PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK && $order->payment_status_id === \App\DataClasses\OrderPaymentStatusesDataClass::STATUS_PAID )

            @if( is_null($order->mono_order_state) )

                <div class="modal fade" id="rejectOrderModal-{{ $order->id }}" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="defaultModalLabel">{{ trans('admin.order_reject') }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">{{ trans('admin.are_you_sure') }}</div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('admin.close') }}</button>
                                <form action="{{ route('admin.order.monobank.reject', ['order' => $order->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">{{ trans('admin.order_reject') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="confirmOrderModal-{{ $order->id }}" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="defaultModalLabel">{{ trans('admin.order_confirm') }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">{{ trans('admin.are_you_sure') }}</div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('admin.close') }}</button>
                                <form action="{{ route('admin.order.monobank.confirm', ['order' => $order->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">{{ trans('admin.order_confirm') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            @elseif( $order->mono_order_state == App\DataClasses\MonoBankOrderStateStatusesDataClass::STATUS_CONFIRMED )
                <div class="modal fade" id="returnOrderModal-{{ $order->id }}" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="defaultModalLabel">{{ trans('admin.order_return') }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">{{ trans('admin.are_you_sure') }}</div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('admin.close') }}</button>
                                <form action="{{ route('admin.order.monobank.return', ['order' => $order->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">{{ trans('admin.order_return') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        @endif

    @endforeach

@endsection
@push('scripts')
    <script>
        $('#status_id').select2({
            theme: 'bootstrap4',
        });
    </script>
@endpush
