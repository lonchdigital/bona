@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                @if(isset($author))
                    <h2 class="page-title">{{ trans('admin.author_edit') }}</h2>
                @else
                    <h2 class="page-title">{{ trans('admin.author_new') }}</h2>
                @endif

                <author-page-edit-form
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    submit-route="{{ isset($author) ? route('admin.author.edit', ['author' => $author]) : route('admin.author.create') }}"

                    @if(isset($author))
                    :author-name="{{ json_encode($author->getTranslations('name')) }}"
                    :author-slug="{{ json_encode($author->slug) }}"
                    :author-job-title="{{ json_encode($author->getTranslations('job_title')) }}"
                    :author-short-description="{{ json_encode($author->getTranslations('short_description')) }}"
                    :author-biography="{{ json_encode($author->getTranslations('biography')) }}"
                    :author-photo="{{ json_encode($author->photo_url) }}"
                    :author-instagram-url="{{ json_encode($author->instagram_url) }}"
                    :author-facebook-url="{{ json_encode($author->facebook_url) }}"
                    :author-linkedin-url="{{ json_encode($author->linkedin_url) }}"
                    :author-meta-title="{{ json_encode($author->getTranslations('meta_title')) }}"
                    :author-meta-description="{{ json_encode($author->getTranslations('meta_description')) }}"
                    :author-meta-keywords="{{ json_encode($author->getTranslations('meta_keywords')) }}"
                    :author-certificates="{{ json_encode($author->certificates->map(fn ($certificate) => [
                        'id' => $certificate->id,
                        'title' => $certificate->getTranslations('title'),
                        'issuer' => $certificate->issuer,
                        'issued_year' => $certificate->issued_year,
                        'image_url' => $certificate->image_url,
                    ])) }}"
                    @endif
                />
            </div>
        </div>
    </div>
@endsection

@section('vue')
    <vue/>
@endsection
