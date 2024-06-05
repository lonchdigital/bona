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

                            @include('pages.store.partials.profile_user_navigation')

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@stop
