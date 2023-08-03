@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">{{ \App\DataClasses\StaticPageTypesDataClass::get($staticPageId)['name'] }}</h2>
                <div class="card shadow mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.static_page_details') }}</strong>
                        <x-admin.multilanguage-switch/>
                    </div>
                    <div class="card-body">
                        <form id="static-page-edit-form" method="POST" action="{{ route('admin.static-pages.edit', ['staticPage' => $staticPageId]) }}">
                            @csrf
                            <div class="tab-content">
                                @foreach($availableLanguages as $availableLanguage)
                                    <input type="hidden" name="content[{{$availableLanguage}}]" value="">
                                    <div language="{{ $availableLanguage }}" class="multilang-content tab-pane fade @if($availableLanguage == app()->getLocale())active show @endif">
                                        <strong>{{ mb_strtoupper($availableLanguage) }}</strong>
                                        <div class="editor" id="editor-{{ $availableLanguage }}" style="min-height:100px;">
                                            {!! $content->where('language', $availableLanguage)->first()?->content !!}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="row pt-2">
                                <div class="col-md-12 text-right">
                                    <a href="{{ route('admin.static-pages.list.page') }}" class="btn btn-secondary">{{ trans('admin.back') }}</a>
                                    <button type="submit" class="btn btn-dark">{{ trans('admin.save') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src='/static-admin/js/quill.min.js'></script>
    <script type="text/javascript">
        const toolbarOptions = [
            [
                {
                    'header': [1, 2, 3, 4, 5, 6, false]
                }],
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote'],
            [
                {
                    'header': 1
                },
                {
                    'header': 2
                }],
            [
                {
                    'list': 'ordered'
                },
                {
                    'list': 'bullet'
                }],
            [
                {
                    'script': 'sub'
                },
                {
                    'script': 'super'
                }],
            [
                {
                    'color': []
                },
            ],
            [
                {
                    'align': []
                }],
            ['clean']
        ];

        let editorsOnPage = [];

        $('.editor').each(function () {
            const quill = new Quill($(this)[0],
                {
                    modules:
                        {
                            toolbar: toolbarOptions
                        },
                    theme: 'snow'
                });
            editorsOnPage.push({editor: $(this), input: $(`input[name="content[${$(this).parent().attr('language')}]"]`)});
        });

        const from = $('#static-page-edit-form').submit(function () {
            editorsOnPage.forEach(function (editor) {
                console.log(editor.editor);
                editor.input.val(editor.editor.find('.ql-editor').html());
            });
        });
    </script>
@endpush
