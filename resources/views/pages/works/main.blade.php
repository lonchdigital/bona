@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . trans('base.blog') }}</title>
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['#' => 'our_works']])

    <!-- ========================  Works ======================== -->
    <section class="blog art-section-pd">

        <div class="container">

            <h1>{{ trans('base.our_works') }}</h1>

            <div class="row">
                @foreach($works as $work)
                    <div class="col-lg-4">
                        <article class="art-post-archive-item">
                            <a data-fancybox="works-gallery" href="{{ $work->image_url }}">
                                <div class="image" style="background-image:url({{ $work->image_url }})"></div>
                                <div class="entry entry-post">

                                    <div class="preview-post-right">
                                        <div class="title">
                                            <h2 class="h5">{{ $work->name }}</h2>
                                        </div>
                                    </div>

                                </div>
                            </a>
                        </article>
                    </div>
                @endforeach
            </div> <!--/row-->

            <!-- === pagination === -->
            {{ $works->links('pagination.common') }}

        </div><!--/container-->
    </section>


    <main class="main blog-p">
        <div class="content">
            @guest
                @if(!\Illuminate\Support\Facades\Session::exists('email_subscription_sent'))
                    <x-email-subscription-form/>
                @endif
            @endguest
        </div>
    </main>
@endsection
