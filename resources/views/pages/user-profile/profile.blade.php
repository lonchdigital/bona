@extends('layouts.store-main')

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['own' => trans('user-profile.profile')]])

    <main class="main pt-5">
        <div class="content">
            <section class="user-profile">
                <div class="container">
                    <div class="row justify-content-md-center">
                        <div class="col-lg-6 mb-5">
                            <h2 class="mt-5 text-center">{{ trans('user-profile.profile') }}</h2>

                            <div class="main-user-navigation">
                                <ul>
{{--                                    <li><a href="{{ route('user.profile.page') }}">{{ trans('user-profile.profile') }}</a></li>--}}
                                    <li><a href="{{ route('profile.edit.page') }}">{{ trans('user-profile.update_user_data') }}</a></li>
                                    <li><a href="{{ route('user.profile.orders.page') }}">{{ trans('user-profile.user_orders') }}</a></li>
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
                </div>
            </section>
        </div>
    </main>
@stop
