@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - HOME' }}</title>
@endsection

@section('content')

    <!-- ========================  Header content ======================== -->


{{--    @dd($slides)--}}

    <section class="header-content">

        <div class="owl-slider">

            @foreach($slides as $slide)
                <div class="item" style="background-image:url({{ $slide->slide_image_url }})">
                    <div class="box">
                        <div class="container">
                            <h2 class="title animated h1" data-animation="fadeInDown">{{ $slide->title }}</h2>
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

    <!-- ========================  Products widget ======================== -->

    <section class="products">

        <div class="container">

            <!-- === header title === -->

            <header>
                <div class="row">
                    <div class="col-md-offset-2 col-md-8 text-center">
                        <h2 class="title">Popular products</h2>
                        <div class="text">
                            <p>Check out our latest collections</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="row">

                <!-- === product-item === -->

                <div class="col-md-4 col-xs-6">

                    <article>
                        <div class="info">
                                <span class="add-favorite added">
                                    <a href="javascript:void(0);" data-title="Add to favorites" data-title-added="Added to favorites list"><i class="icon icon-heart"></i></a>
                                </span>
                            <span>
                                    <a href="#productid1" class="mfp-open" data-title="Quick wiew"><i class="icon icon-eye"></i></a>
                                </span>
                        </div>
                        <div class="btn btn-add">
                            <i class="icon icon-cart"></i>
                        </div>
                        <div class="figure-grid">
                            <div class="image">
                                <a href="#productid1" class="mfp-open">
                                    <img src="assets/images/product-1.png" alt="" width="360" />
                                </a>
                            </div>
                            <div class="text">
                                <h2 class="title h4"><a href="product.html">Green corner</a></h2>
                                <sub>$ 1499,-</sub>
                                <sup>$ 1099,-</sup>
                                <span class="description clearfix">Gubergren amet dolor ea diam takimata consetetur facilisis blandit et aliquyam lorem ea duo labore diam sit et consetetur nulla</span>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- === product-item === -->

                <div class="col-md-4 col-xs-6">
                    <article>
                        <div class="info">
                                <span class="add-favorite">
                                    <a href="javascript:void(0);" data-title="Add to favorites" data-title-added="Added to favorites list"><i class="icon icon-heart"></i></a>
                                </span>
                            <span>
                                    <a href="#productid1" class="mfp-open" data-title="Quick wiew"><i class="icon icon-eye"></i></a>
                                </span>
                        </div>
                        <div class="btn btn-add">
                            <i class="icon icon-cart"></i>
                        </div>
                        <div class="figure-grid">
                            <div class="image">
                                <a href="#productid1" class="mfp-open">
                                    <img src="assets/images/product-2.png" alt="" width="360" />
                                </a>
                            </div>
                            <div class="text">
                                <h2 class="title h4"><a href="product.html">Laura</a></h2>
                                <sub>$ 3999,-</sub>
                                <sup>$ 3499,-</sup>
                                <span class="description clearfix">Gubergren amet dolor ea diam takimata consetetur facilisis blandit et aliquyam lorem ea duo labore diam sit et consetetur nulla</span>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- === product-item === -->

                <div class="col-md-4 col-xs-6">
                    <article>
                        <div class="info">
                                <span class="add-favorite">
                                    <a href="javascript:void(0);" data-title="Add to favorites" data-title-added="Added to favorites list"><i class="icon icon-heart"></i></a>
                                </span>
                            <span>
                                    <a href="#productid1" class="mfp-open" data-title="Quick wiew"><i class="icon icon-eye"></i></a>
                                </span>
                        </div>
                        <div class="btn btn-add">
                            <i class="icon icon-cart"></i>
                        </div>
                        <div class="figure-grid">
                            <span class="label label-warning">New</span>
                            <div class="image">
                                <a href="#productid1" class="mfp-open">
                                    <img src="assets/images/product-3.png" alt="" width="360" />
                                </a>
                            </div>
                            <div class="text">
                                <h2 class="title h4"><a href="product.html">Nude</a></h2>
                                <sup>$ 2999,-</sup>
                                <span class="description clearfix">Gubergren amet dolor ea diam takimata consetetur facilisis blandit et aliquyam lorem ea duo labore diam sit et consetetur nulla</span>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- === product-item === -->

                <div class="col-md-4 col-xs-6">
                    <article>
                        <div class="info">
                                <span class="add-favorite">
                                    <a href="javascript:void(0);" data-title="Add to favorites" data-title-added="Added to favorites list"><i class="icon icon-heart"></i></a>
                                </span>
                            <span>
                                    <a href="#productid1" class="mfp-open" data-title="Quick wiew"><i class="icon icon-eye"></i></a>
                                </span>
                        </div>
                        <div class="btn btn-add">
                            <i class="icon icon-cart"></i>
                        </div>
                        <div class="figure-grid">
                            <div class="image">
                                <a href="#productid1" class="mfp-open">
                                    <img src="assets/images/product-4.png" alt="" width="360" />
                                </a>
                            </div>
                            <div class="text">
                                <h2 class="title h4"><a href="product.html">Aurora</a></h2>
                                <sup>$ 299,-</sup>
                                <span class="description clearfix">Gubergren amet dolor ea diam takimata consetetur facilisis blandit et aliquyam lorem ea duo labore diam sit et consetetur nulla</span>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- === product-item === -->

                <div class="col-md-4 col-xs-6">
                    <article>
                        <div class="info">
                                <span class="add-favorite added">
                                    <a href="javascript:void(0);" data-title="Add to favorites" data-title-added="Added to favorites list"><i class="icon icon-heart"></i></a>
                                </span>
                            <span>
                                    <a href="#productid1" class="mfp-open" data-title="Quick wiew"><i class="icon icon-eye"></i></a>
                                </span>
                        </div>
                        <div class="btn btn-add">
                            <i class="icon icon-cart"></i>
                        </div>
                        <div class="figure-grid">
                            <span class="label label-info">-50%</span>
                            <div class="image">
                                <a href="#productid1" class="mfp-open">
                                    <img src="assets/images/product-5.png" alt="" width="360" />
                                </a>
                            </div>
                            <div class="text">
                                <h2 class="title h4"><a href="product.html">Dining set</a></h2>
                                <sub>$ 1999,-</sub>
                                <sup>$ 1499,-</sup>
                                <span class="description clearfix">Gubergren amet dolor ea diam takimata consetetur facilisis blandit et aliquyam lorem ea duo labore diam sit et consetetur nulla</span>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- === product-item === -->

                <div class="col-md-4 col-xs-6">
                    <article>
                        <div class="info">
                                <span class="add-favorite">
                                    <a href="javascript:void(0);" data-title="Add to favorites" data-title-added="Added to favorites list"><i class="icon icon-heart"></i></a>
                                </span>
                            <span>
                                    <a href="#productid1" class="mfp-open" data-title="Quick wiew"><i class="icon icon-eye"></i></a>
                                </span>
                        </div>
                        <div class="btn btn-add">
                            <i class="icon icon-cart"></i>
                        </div>
                        <div class="figure-grid">
                            <div class="image">
                                <a href="#productid1" class="mfp-open">
                                    <img src="assets/images/product-6.png" alt="" width="360" />
                                </a>
                            </div>
                            <div class="text">
                                <h2 class="title h4"><a href="product.html">Seat chair</a></h2>
                                <sup>$ 896,-</sup>
                                <span class="description clearfix">Gubergren amet dolor ea diam takimata consetetur facilisis blandit et aliquyam lorem ea duo labore diam sit et consetetur nulla</span>
                            </div>
                        </div>
                    </article>
                </div>

            </div> <!--/row-->
            <!-- === button more === -->

            <div class="wrapper-more">
                <a href="products-grid.html" class="btn btn-main">View store</a>
            </div>

            <!-- ========================  Product info popup - quick view ======================== -->

            <div class="popup-main mfp-hide" id="productid1">

                <!-- === product popup === -->

                <div class="product">

                    <!-- === popup-title === -->

                    <div class="popup-title">
                        <div class="h1 title">Laura <small>product category</small></div>
                    </div>

                    <!-- === product gallery === -->

                    <div class="owl-product-gallery">
                        <img src="assets/images/product-1.png" alt="" width="640" />
                        <img src="assets/images/product-2.png" alt="" width="640" />
                        <img src="assets/images/product-3.png" alt="" width="640" />
                        <img src="assets/images/product-4.png" alt="" width="640" />
                    </div>

                    <!-- === product-popup-info === -->

                    <div class="popup-content">
                        <div class="product-info-wrapper">
                            <div class="row">

                                <!-- === left-column === -->

                                <div class="col-sm-6">
                                    <div class="info-box">
                                        <strong>Maifacturer</strong>
                                        <span>Brand name</span>
                                    </div>
                                    <div class="info-box">
                                        <strong>Materials</strong>
                                        <span>Wood, Leather, Acrylic</span>
                                    </div>
                                    <div class="info-box">
                                        <strong>Availability</strong>
                                        <span><i class="fa fa-check-square-o"></i> in stock</span>
                                    </div>
                                </div>

                                <!-- === right-column === -->

                                <div class="col-sm-6">
                                    <div class="info-box">
                                        <strong>Available Colors</strong>
                                        <div class="product-colors clearfix">
                                            <span class="color-btn color-btn-red"></span>
                                            <span class="color-btn color-btn-blue checked"></span>
                                            <span class="color-btn color-btn-green"></span>
                                            <span class="color-btn color-btn-gray"></span>
                                            <span class="color-btn color-btn-biege"></span>
                                        </div>
                                    </div>
                                    <div class="info-box">
                                        <strong>Choose size</strong>
                                        <div class="product-colors clearfix">
                                            <span class="color-btn color-btn-biege">S</span>
                                            <span class="color-btn color-btn-biege checked">M</span>
                                            <span class="color-btn color-btn-biege">XL</span>
                                            <span class="color-btn color-btn-biege">XXL</span>
                                        </div>
                                    </div>
                                </div>

                            </div> <!--/row-->
                        </div> <!--/product-info-wrapper-->
                    </div> <!--/popup-content-->
                    <!-- === product-popup-footer === -->

                    <div class="popup-table">
                        <div class="popup-cell">
                            <div class="price">
                                <span class="h3">$ 1999,00 <small>$ 2999,00</small></span>
                            </div>
                        </div>
                        <div class="popup-cell">
                            <div class="popup-buttons">
                                <a href="product.html"><span class="icon icon-eye"></span> <span class="hidden-xs">View more</span></a>
                                <a href="javascript:void(0);"><span class="icon icon-cart"></span> <span class="hidden-xs">Buy</span></a>
                            </div>
                        </div>
                    </div>

                </div> <!--/product-->
            </div> <!--popup-main-->
        </div> <!--/container-->
    </section>

    <!-- ========================  Stretcher widget ======================== -->

    <section class="stretcher-wrapper">

        <!-- === stretcher header === -->

        <header class="hidden">
            <!--remove class 'hidden'' to show section header -->
            <div class="container">
                <div class="row">
                    <div class="col-md-offset-2 col-md-8 text-center">
                        <h1 class="h2 title">Popular categories</h1>
                        <div class="text">
                            <p>
                                Whether you are changing your home, or moving into a new one, you will find a huge selection of quality living room furniture,
                                bedroom furniture, dining room furniture and the best value at Furniture factory
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- === stretcher === -->

        <ul class="stretcher">

            <!-- === stretcher item === -->

            <li class="stretcher-item" style="background-image:url(assets/images/gallery-1.jpg);">
                <!--logo-item-->
                <div class="stretcher-logo">
                    <div class="text">
                        <span class="f-icon f-icon-bedroom"></span>
                        <span class="text-intro">Bedroom</span>
                    </div>
                </div>
                <!--main text-->
                <figure>
                    <h4>Modern furnishing projects</h4>
                    <figcaption>New furnishing ideas</figcaption>
                </figure>
                <!--anchor-->
                <a href="#">Anchor link</a>
            </li>

            <!-- === stretcher item === -->

            <li class="stretcher-item" style="background-image:url(assets/images/gallery-2.jpg);">
                <!--logo-item-->
                <div class="stretcher-logo">
                    <div class="text">
                        <span class="f-icon f-icon-sofa"></span>
                        <span class="text-intro">Living room</span>
                    </div>
                </div>
                <!--main text-->
                <figure>
                    <h4>Furnishing and complements</h4>
                    <figcaption>Discover the design table collection</figcaption>
                </figure>
                <!--anchor-->
                <a href="#">Anchor link</a>
            </li>

            <!-- === stretcher item === -->

            <li class="stretcher-item" style="background-image:url(assets/images/gallery-3.jpg);">
                <!--logo-item-->
                <div class="stretcher-logo">
                    <div class="text">
                        <span class="f-icon f-icon-office"></span>
                        <span class="text-intro">Office</span>
                    </div>
                </div>
                <!--main text-->
                <figure>
                    <h4>Which is Best for Your Home</h4>
                    <figcaption>Wardrobes vs Walk-In Closets</figcaption>
                </figure>
                <!--anchor-->
                <a href="#">Anchor link</a>
            </li>

            <!-- === stretcher item === -->

            <li class="stretcher-item" style="background-image:url(assets/images/gallery-4.jpg);">
                <!--logo-item-->
                <div class="stretcher-logo">
                    <div class="text">
                        <span class="f-icon f-icon-bathroom"></span>
                        <span class="text-intro">Bathroom</span>
                    </div>
                </div>
                <!--main text-->
                <figure>
                    <h4>Keeping Things Minimal</h4>
                    <figcaption>Creating Your Very Own Bathroom</figcaption>
                </figure>
                <!--anchor-->
                <a href="#">Anchor link</a>
            </li>

            <!-- === stretcher item more=== -->

            <li class="stretcher-item more">
                <div class="more-icon">
                    <span data-title-show="Show more" data-title-hide="+"></span>
                </div>
                <a href="#"></a>
            </li>

        </ul>
    </section>

    <!-- ========================  Blog Block ======================== -->

    <section class="blog blog-block">

        <div class="container">

            <!-- === blog header === -->

            <header>
                <div class="row">
                    <div class="col-md-offset-2 col-md-8 text-center">
                        <h2 class="title">Interior ideas</h2>
                        <div class="text">
                            <p>Keeping things minimal</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="row">

                <!-- === blog item === -->

                <div class="col-sm-4">
                    <article>
                        <a href="article.html">
                            <div class="image">
                                <img src="assets/images/project-1.jpg" alt="" />
                            </div>
                            <div class="entry entry-block">
                                <div class="date">28 Mart 2017</div>
                                <div class="title">
                                    <h2 class="h3">Creating the Perfect Gallery Wall </h2>
                                </div>
                                <div class="description">
                                    <p>
                                        You’ve finally reached this point in your life- you’ve bought your first home, moved
                                        into your very own apartment, moved out of the dorm room or you’re finally downsizing
                                        after all of your kids have left the nest.
                                    </p>
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
                            <div class="image">
                                <img src="assets/images/project-2.jpg" alt="" />
                            </div>
                            <div class="entry entry-block">
                                <div class="date">25 Mart 2017</div>
                                <div class="title">
                                    <h2 class="h3">Making the Most Out of Your Kids Old Bedroom</h2>
                                </div>
                                <div class="description">
                                    <p>
                                        You’ve finally reached this point in your life- you’ve bought your first home, moved
                                        into your very own apartment, moved out of the dorm room or you’re finally downsizing
                                        after all of your kids have left the nest.
                                    </p>
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
                            <div class="image">
                                <img src="assets/images/project-3.jpg" alt="" />
                            </div>
                            <div class="entry entry-block">
                                <div class="date">28 Mart 2017</div>
                                <div class="title">
                                    <h2 class="h3">Have a look at our new projects!</h2>
                                </div>
                                <div class="description">
                                    <p>
                                        You’ve finally reached this point in your life- you’ve bought your first home, moved
                                        into your very own apartment, moved out of the dorm room or you’re finally downsizing
                                        after all of your kids have left the nest.
                                    </p>
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
                <a href="ideas.html" class="btn btn-main">View all posts</a>
            </div>

        </div> <!--/container-->
    </section>

    <!-- ========================  Banner ======================== -->

    <section class="banner" style="background-image:url(assets/images/gallery-4.jpg)">
        <div class="container">
            <div class="row">
                <div class="col-md-offset-2 col-md-8 text-center">
                    <h2 class="title">Our story</h2>
                    <p>
                        We believe in creativity as one of the major forces of progress. With this idea, we traveled throughout Italy to find exceptional
                        artisans and bring their unique handcrafted objects to connoisseurs everywhere.
                    </p>
                    <p><a href="about.html" class="btn btn-clean">Read full story</a></p>
                </div>
            </div>
        </div>
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

@stop
