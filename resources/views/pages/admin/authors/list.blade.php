@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="mb-2 page-title">{{ trans('admin.authors') }}</h2>
                <p></p>
                <div class="row">
                    <div class="col-md-12">
                        <a href="{{ route('admin.author.create.page') }}" class="btn mb-2 btn-dark">{{ trans('admin.authors_create') }}</a>
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
                                <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table class="table datatables" id="dataTable-1">
                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>{{ trans('admin.name') }}</th>
                                                    <th>{{ trans('admin.author_job_title') }}</th>
                                                    <th>{{ trans('admin.author_certificates') }}</th>
                                                    <th class="text-right">{{ trans('admin.action') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($authorsPaginated as $authorItem)
                                                    <tr>
                                                        <td>{{ $authorItem->id }}</td>
                                                        <td>{{ $authorItem->name }}</td>
                                                        <td>{{ $authorItem->job_title }}</td>
                                                        <td>{{ $authorItem->certificates()->count() }}</td>
                                                        <td class="text-right">
                                                            <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="{{ route('admin.author.edit.page', ['author' => $authorItem->id]) }}">{{ trans('admin.edit') }}</a>
                                                                <a class="dropdown-item" href="{{ route('store.author.page', ['authorSlug' => $authorItem->slug]) }}" target="_blank">{{ trans('admin.view') }}</a>
                                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteAuthorModal-{{ $authorItem->id }}">{{ trans('admin.delete') }}</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
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

    @foreach($authorsPaginated as $authorItem)
        <div class="modal fade" id="deleteAuthorModal-{{ $authorItem->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('admin.author_delete') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">{{ trans('admin.author_delete_confirm_text', ['NAME' => $authorItem->name]) }}</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('admin.close') }}</button>
                        <form action="{{ route('admin.author.delete', ['author' => $authorItem->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger">{{ trans('admin.delete') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
