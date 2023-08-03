@extends('layouts.store-main')

@section('content')
    <main class="main pt-5">
        <div class="content">
            <section>
                <div class="container">
                    <div class="row justify-content-md-center">
                        <div class="col-lg-6 mb-5">
                            <h2 class="mt-5 text-center">{{ trans('user-profile.update_user_data') }}</h2>
                            <form action="{{ route('profile.edit') }}" method="POST" class="form-content d-flex justify-content-center m-5 flex-column">

                                @if(Session::has('message'))
                                <div class="p-2 mb-1 font-weight-bold text-uppercase text-white bg-success-custom">
                                    {{ Session::get('message') }}
                                </div>
                                @endif

                                @csrf

                                <div class="w-full d-flex justify-content-lg-around flex-column flex-lg-row">
                                    <div class="form-group lg-w-45">
                                        <label class="custom-control-label2" for="first_name">{{ trans('auth.first_name') }}</label>
                                        <input class="form-control" placeholder="{{ trans('auth.first_name_placeholder') }}" type="text" name="first_name" value="{{ old('first_name') ? old('first_name') : $user->first_name }}" id="first_name"/>
                                        @error('first_name')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group lg-w-45">
                                        <label class="custom-control-label2" for="last_name">{{ trans('auth.last_name') }}</label>
                                        <input class="form-control" placeholder="{{ trans('auth.last_name_placeholder') }}" type="text" name="last_name" value="{{ old('last_name') ?  old('last_name') : $user->last_name }}" id="last_name"/>
                                        @error('last_name')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="w-full d-flex justify-content-lg-around flex-column flex-lg-row">
                                    <div class="form-group lg-w-45">
                                        <label class="custom-control-label2" for="email">{{ trans('auth.email') }}</label>
                                        <input class="form-control" placeholder="{{ trans('auth.email_placeholder') }}" type="text" name="email" value="{{ old('email') ? old('email') : $user->email }}" id="email"/>
                                        @error('email')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group lg-w-45">
                                        <label class="custom-control-label2" for="phone">{{ trans('auth.phone') }}</label>
                                        <input id="phone" class="form-control" placeholder="{{ trans('auth.phone_placeholder') }}" type="text" name="phone" value="{{ old('phone') ?  old('phone') : $user->phone }}"/>
                                        @error('phone')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <button class="mt-5 btn btn-outline-black" type="submit">{{ trans('user-profile.update') }}</button>
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('profile.edit-password.page') }}" class="mt-1 btn btn-dark">{{ trans('user-profile.update_password') }}</a>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@stop
@push('scripts')
    <script type='text/javascript' src="{{ asset('/static/js/jquery.maskedinput.min.js') }}"></script>
    <script type='text/javascript'>
        $(document).ready(function(){
            $("#phone").inputmask({mask:"+38(099)999-99-99"});
        });

    </script>
@endpush
