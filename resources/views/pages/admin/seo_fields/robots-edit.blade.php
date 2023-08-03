@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">{{ trans('admin.robots_txt_edit') }}</h2>
                <div class="card shadow mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.robots_txt_info') }}</strong>
                    </div>
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
                        <x-admin.reactive-form method="POST" action="{{ route('admin.seo.robots-txt.edit') }}">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="slug">{{ trans('admin.robots_txt') }} <strong class="text-danger">*</strong></label>
                                <textarea class="form-control" name="content" rows="4">{{ $content }}</textarea>
                                <div class="mt-1 text-danger ajaxError" id="error-field-slug"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <button type="submit" class="btn btn-dark">{{ trans('admin.save') }}</button>
                                </div>
                            </div>
                        </x-admin.reactive-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

