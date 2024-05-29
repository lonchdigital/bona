@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">{{ trans('admin.visit_request_id') }}{{ $visitRequest->id }}</h2>
                <div class="card shadow mb-4">
                    <div id="form-header" class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.order_details') }}</strong>
                    </div>
                    <div class="card-body">
                        <x-admin.reactive-form method="POST" action="{{ route('admin.visit-request.edit', ['visitRequest' => $visitRequest->id]) }}">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="status_id">{{ trans('admin.status') }} <strong
                                        class="text-danger">*</strong></label>
                                <select class="form-control select2" name="status_id" id="status_id">
                                    @foreach(\App\DataClasses\VisitRequestStatusesDataClass::get() as $status)
                                        <option @if($status['id'] === $visitRequest->status_id) selected @endif value="{{ $status['id'] }}">{{ $status['name'] }}</option>
                                    @endforeach
                                </select>
                                <div class="mt-1 text-danger ajaxError" id="error-field-status_id"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <a href="{{ route('admin.visit-request.list.page') }}" class="btn btn-secondary">{{ trans('admin.back') }}</a>
                                    <button type="submit" class="btn btn-dark">{{ trans('admin.save') }}</button>
                                </div>
                            </div>
                            <p>
                                <strong>
                                    {{ trans('admin.visit_request_information') }}:
                                </strong>
                                @if( !is_null($visitRequest->form_title) )
                                    <span>{{ $visitRequest->form_title }}</span>
                                @endif
                            </p>

                            <div class="mb-3">
                                <striong class="text-dark">{{ trans('admin.visit_request_name') }}</striong>
                                <div class="mt-1">{{ $visitRequest->name }}</div>
                            </div>
                            <div class="mb-3">
                                <striong class="text-dark">{{ trans('admin.visit_request_phone') }}</striong>
                                <div class="mt-1">{{ $visitRequest->phone }}</div>
                            </div>

                        </x-admin.reactive-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript">
        $('.select2').each(function () {
            $(this).select2({
                theme: 'bootstrap4',
            });
        })
    </script>
@endpush
