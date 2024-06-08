@extends('layouts.store-main')

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['own' => trans('auth.reset_password_title')]])

    <main class="main pt-5">
        <div class="content">
            <section>
                <div class="container">
                    <div class="row justify-content-md-center">
                        <div class="col-lg-4 mb-5">
                            <h2 class="mt-5 text-center">{{ trans('auth.reset_password_title') }}</h2>
                            <p class="text-center">{{ trans('auth.reset_password_text') }}</p>
                            <form action="{{ route('auth.forgot-password') }}" method="POST" class="form-content d-flex justify-content-center m-5 flex-column">
                                @csrf
                                <div class="form-group">
                                    <label class="custom-control-label2" for="email">{{ trans('auth.email') }}</label>
                                    <input id="email" class="art-form-light-control" placeholder="{{ trans('auth.email_placeholder') }}" type="text" name="email" value="{{ old('email') }}"/>
                                    @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                @error('password')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror

                                <button class="btn btn-main" type="submit">{{ trans('auth.reset_password_call_to_action') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@stop
