@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - HOME' }}</title>
@endsection

@section('content')

    <!-- ========================  Header content ======================== -->

    <section class="header-content">

        <div class="owl-slider">

            @foreach($slides as $slide)
                <div class="item" style="background-image:url({{ $slide->slide_image_url }})">
                    <div class="box">
                        <div class="container">
                            <h2 class="title animated h1" data-animation="fadeInDown">{{ $slide->title }}</h2>
                            <div class="animated" data-animation="fadeInUp">{{ $slide->description }}</div>
                            <div class="animated" data-animation="fadeInUp">
                                <a href="{{ $slide->button_url }}" target="_blank" class="btn{{ $loop->first ? ' btn-main' : ' btn-clean' }}" ><i class="icon icon-cart"></i>{{ ' ' . $slide->button_text }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

        </div> <!--/owl-slider-->
    </section>

    <!-- ========================  Icons slider ======================== -->

    <section class="owl-icons-wrapper owl-icons-frontpage">

        <!-- === header === -->

        <header class="hidden">
            <h2>Product categories</h2>
        </header>

        <div class="container">

            <div class="owl-icons">

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-sofa"></i>
                        <figcaption>Sofa</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-armchair"></i>
                        <figcaption>Armchairs</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-chair"></i>
                        <figcaption>Chairs</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-dining-table"></i>
                        <figcaption>Dining tables</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-media-cabinet"></i>
                        <figcaption>Media storage</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-table"></i>
                        <figcaption>Tables</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-bookcase"></i>
                        <figcaption>Bookcase</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-bedroom"></i>
                        <figcaption>Bedroom</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-nightstand"></i>
                        <figcaption>Nightstand</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-children-room"></i>
                        <figcaption>Children room</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-kitchen"></i>
                        <figcaption>Kitchen</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-bathroom"></i>
                        <figcaption>Bathroom</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-wardrobe"></i>
                        <figcaption>Wardrobe</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-shoe-cabinet"></i>
                        <figcaption>Shoe cabinet</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-office"></i>
                        <figcaption>Office</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-bar-set"></i>
                        <figcaption>Bar sets</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-lightning"></i>
                        <figcaption>Lightning</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-carpet"></i>
                        <figcaption>Varpet</figcaption>
                    </figure>
                </a>

                <!-- === icon item === -->

                <a href="#">
                    <figure>
                        <i class="f-icon f-icon-accessories"></i>
                        <figcaption>Accessories</figcaption>
                    </figure>
                </a>

            </div> <!--/owl-icons-->
        </div> <!--/container-->
    </section>

    <!-- ========================  Products Category widget ======================== -->

    <section class="art-products-category">

        <div class="container">

            <!-- === header title === -->

            <header>
                <div class="row">
                    <div class="col-md-offset-2 col-md-8 text-center">
                        <h2 class="title">ДВЕРІ ЗА ТИПОМ</h2>
                        <div class="text">
                            <p>Категорії дверей</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="art-category-list">

                @foreach($productTypes as $productType)
                    <!-- === product-item === -->
                    <div class="art-category-item">
                        <article>
                            <div class="figure-grid">
                                <div class="image">
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug]) }}">
                                        <img src="{{ $productType->image_url }}" alt="Product Type Image">
                                        <div class="text">
                                            <h4 class="title">{{ $productType->name }}</h4>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach

            </div> <!--/row-->


        </div> <!--/container-->
    </section>

    <!-- ======================== New Products  ======================== -->

    <section class="products">

        <div class="container">

            <!-- === header title === -->

            <header>
                <div class="row">
                    <div class="col-md-offset-2 col-md-8 text-center">
                        <h2 class="title">Новинки</h2>
                        <div class="text">
                            <p>Check out our latest collections</p>
                        </div>
                    </div>
                </div>
            </header>


            <div class="art-products-slider-wrapper">

                <div class="art-products-owl-items">
                    @foreach($homeNewProducts as $product)
                        <div class="item">

                            <div class="art-product-item">
                                <div class="art-product-data">
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->product->slug]) }}" class="">
                                        <div class="image">
                                            {{--                                        <img src="{{ $product->product->preview_image_url }}" alt="">--}}
                                            <img src="{{ $product->product->main_image_url }}" alt="">
                                        </div>
                                        <div class="text">
                                            <h2 class="product-title">{{ $product->product->name }}</h2>
                                            <span class="price-wrapper">
                                            <span class="price">{{ $product->product->price }}</span>
                                            <span class="currency">{{ $baseCurrency->name_short }}</span>
                                        </span>
                                        </div>
                                    </a>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

            </div> <!--/row-->



        </div> <!--/container-->
    </section>

    <!-- ======================== Contact Form ======================== -->

    <section class="art-contact-form-section" style="background-image:url({{ asset('storage/bg-images/form-bg.png') }})">
        <div class="container">
            <div class="row">
                <div class="col-md-offset-2 col-md-8 text-center">
                    <h2 class="title">Не знаєте які двері обрати?</h2>
                    <p>
                        We believe in creativity as one of the major forces of progress. With this idea, we traveled throughout Italy
                        to find exceptional artisans and bring their unique handcrafted objects to connoisseurs everywhere.
                    </p>

                    <form action="#" method="post" class="art-contact-form">
                        @csrf
                        <p class="art-fields-row">
                            <input type="text" class="art-light-field" placeholder="Ім’я">
                            <input type="text" class="art-light-field" placeholder="Телефон">
                        </p>
<!--                        <div class="checkbox">
                            <input type="checkbox" name="agree" value="value">
                            <label for="fieldName">Даю згоду на обробку персональних даних</label>
                        </div>-->
                        <p><a href="about.html" class="btn btn-clean">Відправити</a></p>
                    </form>


                </div>
            </div>
        </div>
    </section>

    <!-- ======================== Best Sales Products  ======================== -->

    <section class="products">

        <div class="container">

            <!-- === header title === -->

            <header>
                <div class="row">
                    <div class="col-md-offset-2 col-md-8 text-center">
                        <h2 class="title">Лiдери продажу</h2>
                        <div class="text">
                            <p>Check out our latest collections</p>
                        </div>
                    </div>
                </div>
            </header>


            <div class="art-products-slider-wrapper">

                <div class="art-products-owl-items">
                    @foreach($homeBestSalesProducts as $product)
                        <div class="item">

                            <div class="art-product-item">
                                <div class="art-product-data">
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->product->slug]) }}" class="">
                                        <div class="image">
                                            {{--                                        <img src="{{ $product->product->preview_image_url }}" alt="">--}}
                                            <img src="{{ $product->product->main_image_url }}" alt="">
                                        </div>
                                        <div class="text">
                                            <h2 class="product-title">{{ $product->product->name }}</h2>
                                            <span class="price-wrapper">
                                            <span class="price">{{ $product->product->price }}</span>
                                            <span class="currency">{{ $baseCurrency->name_short }}</span>
                                        </span>
                                        </div>
                                    </a>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

            </div> <!--/row-->



        </div> <!--/container-->
    </section>

    <!-- ========================  Instagram ======================== -->

    <section class="instagram">

        <!-- === instagram header === -->

        <header>
            <div class="container">
                <div class="row">
                    <div class="col-md-offset-2 col-md-8 text-center">
                        <h2 class="h2 title">Follow us <i class="fa fa-instagram fa-2x"></i> Instagram </h2>
                        <div class="text">
                            <p>@InstaFurnitureFactory</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- === instagram gallery === -->

        <div class="gallery clearfix">
            <a class="item" href="#">
                <img src="assets/images/square-1.jpg" alt="Alternate Text" />
            </a>
            <a class="item" href="#">
                <img src="assets/images/square-2.jpg" alt="Alternate Text" />
            </a>
            <a class="item" href="#">
                <img src="assets/images/square-3.jpg" alt="Alternate Text" />
            </a>
            <a class="item" href="#">
                <img src="assets/images/square-4.jpg" alt="Alternate Text" />
            </a>
            <a class="item" href="#">
                <img src="assets/images/square-5.jpg" alt="Alternate Text" />
            </a>
            <a class="item" href="#">
                <img src="assets/images/square-6.jpg" alt="Alternate Text" />
            </a>

        </div> <!--/gallery-->

    </section>

    <!-- ========================  Blog ======================== -->

    <section class="blog">

        <div class="container">

            <!-- === blog header === -->

            <header>
                <div class="row">
                    <div class="col-md-offset-2 col-md-8 text-center">
                        <h1 class="h2 title">Blog</h1>
                        <div class="text">
                            <p>Latest news from the blog</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="row">

                <!-- === blog item === -->

                <div class="col-sm-4">
                    <article>
                        <a href="article.html">
                            <div class="image" style="background-image:url(assets/images/blog-1.jpg)">
                                <img src="assets/images/blog-1.jpg" alt="" />
                            </div>
                            <div class="entry entry-table">
                                <div class="date-wrapper">
                                    <div class="date">
                                        <span>MAR</span>
                                        <strong>08</strong>
                                        <span>2017</span>
                                    </div>
                                </div>
                                <div class="title">
                                    <h2 class="h5">The 3 Tricks that Quickly Became Rules</h2>
                                </div>
                            </div>
                            <div class="show-more">
                                <span class="btn btn-main btn-block">Read more</span>
                            </div>
                        </a>
                    </article>
                </div>

                <!-- === blog item === -->

                <div class="col-sm-4">
                    <article>
                        <a href="article.html">
                            <div class="image" style="background-image:url(assets/images/blog-2.jpg)">
                                <img src="assets/images/blog-1.jpg" alt="" />
                            </div>
                            <div class="entry entry-table">
                                <div class="date-wrapper">
                                    <div class="date">
                                        <span>MAR</span>
                                        <strong>03</strong>
                                        <span>2017</span>
                                    </div>
                                </div>
                                <div class="title">
                                    <h2 class="h5">Decorating When You're Starting Out or Starting Over</h2>
                                </div>
                            </div>
                            <div class="show-more">
                                <span class="btn btn-main btn-block">Read more</span>
                            </div>
                        </a>
                    </article>
                </div>

                <!-- === blog item === -->

                <div class="col-sm-4">
                    <article>
                        <a href="article.html">
                            <div class="image" style="background-image:url(assets/images/blog-8.jpg)">
                                <img src="assets/images/blog-8.jpg" alt="" />
                            </div>
                            <div class="entry entry-table">
                                <div class="date-wrapper">
                                    <div class="date">
                                        <span>MAR</span>
                                        <strong>01</strong>
                                        <span>2017</span>
                                    </div>
                                </div>
                                <div class="title">
                                    <h2 class="h5">What does your favorite dining chair say about you?</h2>
                                </div>
                            </div>
                            <div class="show-more">
                                <span class="btn btn-main btn-block">Read more</span>
                            </div>
                        </a>
                    </article>
                </div>

            </div> <!--/row-->
            <!-- === button more === -->

            <div class="wrapper-more">
                <a href="blog-grid.html" class="btn btn-main">View all posts</a>
            </div>

        </div> <!--/container-->
    </section>

    <!-- ======================== Quotes ======================== -->

    <section class="quotes quotes-slider" style="background-image:url({{ asset('storage/bg-images/testimonials-bg.png') }})">
        <div class="container">

            <!-- === quotes header === -->
            <header>
                <h2 class="title">What clients say</h2>
            </header>

            <div class="row">

                <div class="quote-carousel art-quote-carousel-home">

                    @foreach($homeTestimonials as $testimonial)
                        <div class="quote">
                            <div class="image">
                                <img src="{{ $testimonial->testimonial_image_url }}" alt="Testimonial image">
                            </div>
                            <div class="name">
                                <h4>{{ $testimonial->name }}</h4>
                            </div>
                            <div class="text">
                                <p>{{ $testimonial->review }}</p>
                            </div>
                        </div>
                    @endforeach

                </div> <!--/quote-carousel-->
            </div> <!--/row-->
        </div> <!--/container-->
    </section>

    <!-- ======================== FAQs ======================== -->

    <section class="faqs-section">
        <div class="container">

            <header>
                <div class="row">
                    <div class="col-md-offset-2 col-md-8 text-center">
                        <h2 class="title">FAQs</h2>
                        <div class="text">
                            <p>Check out our latest collections</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="accordion-faqs">

                <div class="faq-col">
                    @foreach($faqs as $index => $faq)
                        @if($index % 2 == 0)
                            <div class="accordion-item-wrapper">
                                <button class="accordion">
                                    <span class="question">{{ $faq->question }}</span>
                                </button>
                                <div class="art-panel">
                                    <div class="panel-data">{{ $faq->answer }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="faq-col">
                    @foreach($faqs as $index => $faq)
                        @if($index % 2 != 0)
                            <div class="accordion-item-wrapper">
                                <button class="accordion">
                                    <span class="question">{{ $faq->question }}</span>
                                </button>
                                <div class="art-panel">
                                    <div class="panel-data">{{ $faq->answer }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

            </div>

        </div>
    </section>

    <!-- ======================== SEO ======================== -->

    <section class="seo-section">
        <div class="container">

            <header>
                <div class="row">
                    <div class="col-md-offset-2 col-md-8 text-center">
                        <h2 class="title">{{$seoText['title']}}</h2>
                        <div class="text">
                            <p>Our seo text</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="seo-content">
                {!! $seoText['content'] !!}
            </div>

        </div>
    </section>


@stop
