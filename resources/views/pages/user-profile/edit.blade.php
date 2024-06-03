@extends('layouts.store-main')

@section('content')

    @include('pages.store.partials.page_header', ['links' => [
    'own-2' => trans('user-profile.profile'),
    'own' => trans('user-profile.update_user_data')
    ]])

    <main class="main pt-5">
        <div class="content">
            <section class="user-profile">
                <div class="container">

                    <div class="row">
                        <div class="mb-12 w-100">
                            <div class="main-user-navigation">
                                <ul>
                                    <li @if(Route::currentRouteName() === 'user.profile.orders.page') class="active" @endif><a href="{{ route('user.profile.orders.page') }}">{{ trans('user-profile.user_orders') }}</a></li>
                                    <li @if(Route::currentRouteName() === 'profile.edit.page') class="active" @endif><a href="{{ route('profile.edit.page') }}">{{ trans('user-profile.update_user_data') }}</a></li>
                                    <li class="sub-menu-list-item">
                                        <form action="<?php echo e(route('auth.logout')); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <button class="btn btn-go-to-cart btn-block">
                                                <span><?php echo e(trans('auth.logout')); ?></span>
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-md-center">

                        <div class="col-lg-6 mb-5">

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
                                        <p class="user-email-field">{{ $user->email }}</p>
                                    </div>

                                    <div class="form-group lg-w-45">
                                        <label class="custom-control-label2" for="phone">{{ trans('auth.phone') }}</label>
                                        <input id="phone" class="form-control" placeholder="{{ trans('auth.phone_placeholder') }}" type="text" name="phone" value="{{ old('phone') ?  old('phone') : $user->phone }}"/>
                                        @error('phone')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <button class="mt-5 btn btn-main" type="submit">{{ trans('user-profile.update') }}</button>
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
