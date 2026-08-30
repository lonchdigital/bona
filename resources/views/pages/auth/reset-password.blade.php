@extends('layouts.store-main')

@section('title')
    <title>{{ trans('auth.set_new_password') }} | {{ config('app.name') }}</title>
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
    @include('pages.store.partials.page_header', ['links' => ['own' => trans('auth.set_new_password')]])

    <main class="main pt-5">
        <div class="content">
            <section>
                <div class="container">
                    <div class="row justify-content-md-center">
                        <div class="col-lg-4 mb-5">
                            <h1 class="mt-5 text-center">{{ trans('auth.set_new_password') }}</h1>
                            <form action="{{ route('auth.reset-password') }}" method="POST" class="form-content d-flex justify-content-center m-5 flex-column">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">

                                <div class="form-group">
                                    <label for="email">{{ trans('auth.email') }}</label>
                                    <input id="email" class="art-form-light-control" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email">
                                    @error('email')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>

                                <div class="form-group">
                                    <label for="password">{{ trans('auth.password') }}</label>
                                    <input id="password" class="art-form-light-control" type="password" name="password" required autocomplete="new-password">
                                    @error('password')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmation">{{ trans('auth.password_confirmation') }}</label>
                                    <input id="password_confirmation" class="art-form-light-control" type="password" name="password_confirmation" required autocomplete="new-password">
                                </div>

                                <button class="btn btn-main" type="submit">{{ trans('auth.save_new_password') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@stop
