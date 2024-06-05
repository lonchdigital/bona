@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">{{ trans('admin.application_config') }}</h2>

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

                <application-configs-page-edit-form
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    submit-route="{{ route('admin.application-config.edit') }}"


                    @if( !is_null($applicationConfig) )
                        @if(array_key_exists('logoLight', $applicationConfig) && !is_null($applicationConfig['logoLight']))
                            :logo-light-url="{{ json_encode( '/storage/' . $applicationConfig['logoLight']) }}"
                        @endif

                        @if(array_key_exists('logoDark', $applicationConfig) && !is_null($applicationConfig['logoDark']))
                            :logo-dark-url="{{ json_encode( '/storage/' . $applicationConfig['logoDark']) }}"
                        @endif

                        @if(array_key_exists('instagram', $applicationConfig))
                            :instagram="{{ json_encode($applicationConfig['instagram']) }}"
                        @endif

                        @if(array_key_exists('telegram', $applicationConfig))
                            :telegram="{{ json_encode($applicationConfig['telegram']) }}"
                        @endif

                        @if(array_key_exists('viber', $applicationConfig))
                            :viber="{{ json_encode($applicationConfig['viber']) }}"
                        @endif

                        @if(array_key_exists('facebook', $applicationConfig))
                            :facebook="{{ json_encode($applicationConfig['facebook']) }}"
                        @endif

                        @if(array_key_exists('phoneOne', $applicationConfig))
                            :phone-one="{{ json_encode($applicationConfig['phoneOne']) }}"
                        @endif

                        @if(array_key_exists('footerText', $applicationConfig))
                            :footer-text="{{ json_encode($applicationConfig['footerText']) }}"
                        @endif


                        @if(array_key_exists('formTitle', $applicationConfig))
                            :form-title="{{ json_encode($applicationConfig['formTitle']) }}"
                        @endif
                        @if(array_key_exists('formText', $applicationConfig))
                            :form-text="{{ json_encode($applicationConfig['formText']) }}"
                        @endif
                        @if(array_key_exists('formImage', $applicationConfig) && !is_null($applicationConfig['formImage']))
                            :form-image-url="{{ json_encode( '/storage/' . $applicationConfig['formImage']) }}"
                        @endif

                        @if(array_key_exists('authorName', $applicationConfig))
                            :author-name="{{ json_encode($applicationConfig['authorName']) }}"
                        @endif
                        @if(array_key_exists('authorDescription', $applicationConfig))
                            :author-description="{{ json_encode($applicationConfig['authorDescription']) }}"
                        @endif
                        @if(array_key_exists('authorAvatar', $applicationConfig) && !is_null($applicationConfig['authorAvatar']))
                            :author-avatar-url="{{ json_encode( '/storage/' . $applicationConfig['authorAvatar']) }}"
                        @endif

                    @endif
                />

            </div>
        </div>
    </div>
@endsection
@section('vue')
    <vue/>
@endsection
