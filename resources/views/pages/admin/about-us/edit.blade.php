@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">{{trans('base.about_us')}}</h2>

                <about-us-page-edit-form
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    submit-route="{{ route('admin.about-us.edit') }}"

                    @if( !is_null($aboutUsConfig) )
                        :page-meta-title="{{ json_encode($aboutUsConfig->getTranslations('meta_title')) }}"
                        :page-meta-description="{{ json_encode($aboutUsConfig->getTranslations('meta_description')) }}"
                        :page-meta-keywords="{{ json_encode($aboutUsConfig->getTranslations('meta_keywords')) }}"
                        :page-meta-tags="{{ json_encode($aboutUsConfig->meta_tags) }}"

                        :title="{{ json_encode($aboutUsConfig->getTranslations('title')) }}"
                        :description="{{ json_encode($aboutUsConfig->getTranslations('description')) }}"
                        :button-text="{{ json_encode($aboutUsConfig->getTranslations('button_text')) }}"
                        :button-url="{{ json_encode($aboutUsConfig->button_url) }}"

                        @if(!empty($aboutUsConfig->image))
                            image-url="{{ $aboutUsConfig->image_url }}"
                        @endif

                        :video-iframe="{{ json_encode($aboutUsConfig->iframe) }}"

                        :facts-title="{{ json_encode($aboutUsConfig->getTranslations('facts_title')) }}"
                        :history-title="{{ json_encode($aboutUsConfig->getTranslations('history_title')) }}"
                        :history-text="{{ json_encode($aboutUsConfig->getTranslations('history_text')) }}"
                        :steps-title="{{ json_encode($aboutUsConfig->getTranslations('steps_title')) }}"
                        :team-title="{{ json_encode($aboutUsConfig->getTranslations('team_title')) }}"
                        :cta-title="{{ json_encode($aboutUsConfig->getTranslations('cta_title')) }}"
                        :cta-text="{{ json_encode($aboutUsConfig->getTranslations('cta_text')) }}"
                        :cta-button-text="{{ json_encode($aboutUsConfig->getTranslations('cta_button_text')) }}"
                        :cta-button-url="{{ json_encode($aboutUsConfig->cta_button_url) }}"
                    @endif

                    :page-facts="{{ json_encode($aboutUsFacts->map(fn ($fact) => [
                        'id' => $fact->id,
                        'value' => $fact->value,
                        'label' => $fact->getTranslations('label'),
                    ])) }}"
                    :page-steps="{{ json_encode($aboutUsSteps->map(fn ($step) => [
                        'id' => $step->id,
                        'title' => $step->getTranslations('title'),
                        'text' => $step->getTranslations('text'),
                    ])) }}"
                    :page-team="{{ json_encode($aboutUsTeam->map(fn ($member) => [
                        'id' => $member->id,
                        'name' => $member->getTranslations('name'),
                        'role' => $member->getTranslations('role'),
                        'experience' => $member->getTranslations('experience'),
                        'quote' => $member->getTranslations('quote'),
                        'photo_url' => $member->photo_url,
                    ])) }}"
                />

            </div>
        </div>
    </div>
@endsection
@section('vue')
    <vue/>
@endsection
