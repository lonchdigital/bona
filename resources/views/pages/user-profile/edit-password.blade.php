@extends('layouts.store-main')

@section('content')
    <main class="main pt-5">
        <div class="content">
            <section>
                <div class="container">
                    <div class="row justify-content-md-center">
                        <div class="col-lg-4 mb-5">
                            <h2 class="mt-5 text-center">{{ trans('user-profile.update_password') }}</h2>
                            <form action="{{ route('profile.edit-password') }}" method="POST" class="form-content d-flex justify-content-center m-5 flex-column">
                                @if(Session::has('message'))
                                    <div class="p-2 mb-1 font-weight-bold text-uppercase text-white bg-success-custom">
                                        {{ Session::get('message') }}
                                    </div>
                                @endif

                                @csrf

                                <!-- current password start -->
                                <div class="form-group">
                                    <label class="custom-control-label2" for="current_password">{{ trans('user-profile.current_password') }}</label>
                                    <div class="d-flex flex-row align-items-center justify-content-end password-input">
                                        <input id="current_password" class="form-control block w-full" placeholder="{{ trans('user-profile.current_password') }}" type="password" name="current_password"/>
                                        <a href="#" class="position-absolute mr-2 bg-white password-eye-toggle">
                                            <svg class="password-eye-icon">
                                                <use class="password-eye-icon-use" href="{{ asset('/static/img/icon.svg#i-eye-off') }}"></use>
                                            </svg>
                                        </a>
                                    </div>
                                    @error('current_password')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- current password end -->

                                <!-- new password start -->
                                <div class="form-group">
                                    <label class="custom-control-label2" for="new_password">{{ trans('user-profile.new_password') }}</label>
                                    <div class="d-flex flex-row align-items-center justify-content-end password-input">
                                        <input id="new_password" class="form-control block w-full" placeholder="{{ trans('user-profile.new_password') }}" type="password" name="new_password"/>
                                        <a href="#" class="position-absolute mr-2 bg-white password-eye-toggle">
                                            <svg class="password-eye-icon">
                                                <use class="password-eye-icon-use" href="{{ asset('/static/img/icon.svg#i-eye-off') }}"></use>
                                            </svg>
                                        </a>
                                    </div>
                                    @error('new_password')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- new password end -->

                                <!-- password confirmation start -->
                                <div class="form-group">
                                    <label class="custom-control-label2" for="password_confirmation">{{ trans('auth.password_confirmation') }}</label>
                                    <div class="d-flex flex-row align-items-center justify-content-end password-input">
                                        <input id="password_confirmation" class="form-control block w-full" placeholder="{{ trans('auth.password_confirmation') }}" type="password" name="password_confirmation"/>
                                        <a href="#" class="position-absolute mr-2 bg-white password-eye-toggle">
                                            <svg class="password-eye-icon">
                                                <use class="password-eye-icon-use" href="{{ asset('/static/img/icon.svg#i-eye-off') }}"></use>
                                            </svg>
                                        </a>
                                    </div>
                                    @error('password_confirmation')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- password confirmation end -->

                                <button class="btn btn-outline-black" type="submit">{{ trans('user-profile.update_password') }}</button>
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
