@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . trans('base.blog') }}</title>
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['#' => 'our_works']])

    <!-- ========================  Works ======================== -->
    <section class="blog art-section-pd">

        <div class="container">

            <div class="row">
                <header class=" col-12 art-header-left">
                    <div>
                        <h2 class="title">{{ trans('base.our_works') }}</h2>
                    </div>
                </header>
            </div>

            <div class="row">
                @if( count($works) > 0 )
                    <div class="art-blog-archive-wrapper">
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
                    </div>
                @else
                    <div class="col-12">
                        <p class="nothing-found-text mt-5">{{ trans('base.nothing_found') }}</p>
                    </div>
                @endif
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
