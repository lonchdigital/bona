@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="mb-2 page-title">{{ trans('admin.pages') }}</h2>
                <p class="card-text">{{ trans('admin.pages_description') }}</p>

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

                                                    <tr>
                                                        <td>#</td>
                                                        <td><a href="{{ route('admin.home-page.edit.page') }}">{{ trans('admin.home_page') }}</a></td>
                                                        <td>#</td>
                                                        <td class="text-right">
                                                            <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="{{ route('admin.home-page.edit.page') }}">{{ trans('admin.edit') }}</a>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>#</td>
                                                        <td><a href="{{ route('admin.services.edit.page') }}">{{ trans('admin.services') }}</a></td>
                                                        <td>#</td>
                                                        <td class="text-right">
                                                            <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="{{ route('admin.services.edit.page') }}">{{ trans('admin.edit') }}</a>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>#</td>
                                                        <td><a href="{{ route('admin.delivery.edit.page') }}">{{ trans('admin.delivery') }}</a></td>
                                                        <td>#</td>
                                                        <td class="text-right">
                                                            <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="{{ route('admin.delivery.edit.page') }}">{{ trans('admin.edit') }}</a>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>#</td>
                                                        <td><a href="{{ route('admin.about-us.edit.page') }}">{{ trans('admin.about-us') }}</a></td>
                                                        <td>#</td>
                                                        <td class="text-right">
                                                            <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="{{ route('admin.about-us.edit.page') }}">{{ trans('admin.edit') }}</a>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>#</td>
                                                        <td><a href="{{ route('admin.contacts.edit.page') }}">{{ trans('admin.contacts') }}</a></td>
                                                        <td>#</td>
                                                        <td class="text-right">
                                                            <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="{{ route('admin.contacts.edit.page') }}">{{ trans('admin.edit') }}</a>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>#</td>
                                                        <td><a href="{{ route('admin.blog-page.edit.page') }}">{{ trans('admin.blog') }}</a></td>
                                                        <td>#</td>
                                                        <td class="text-right">
                                                            <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="{{ route('admin.blog-page.edit.page') }}">{{ trans('admin.edit') }}</a>
                                                            </div>
                                                        </td>
                                                    </tr>


                                                    @foreach(\App\DataClasses\StaticPageTypesDataClass::get() as $staticPage)
                                                        <tr>
                                                            <td>{{ $staticPage['id'] }}</td>
                                                            <td><a href="{{ route('admin.static-pages.edit.page', ['staticPage' => $staticPage['id']]) }}">{{ $staticPage['name'] }}</a></td>
                                                            <td>#</td>
                                                            <td class="text-right">
                                                                <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-right">
                                                                    <a class="dropdown-item" href="{{ route('admin.static-pages.edit.page', ['staticPage' => $staticPage['id']]) }}">{{ trans('admin.edit') }}</a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach


                                                </tbody>
                                            </table>
                                            <!-- table end -->
                                        </div>
                                    </div>
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
    <script type='text/javascript'>
    </script>
@endpush
