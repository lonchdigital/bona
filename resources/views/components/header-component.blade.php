<!-- ======================== Page header ======================== -->
<section class="main-header" style="background-image:url({{ asset('storage/bg-images/header-bg.png') }})"></section>
<header class="art-page-header">
    <div class="container">
        <ol class="breadcrumb breadcrumb-inverted font-two" itemscope itemtype="https://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}"><span class="icon icon-home"></span></a>
            </li>
            @foreach ($data as $key => $value)
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <span class="active" itemprop="name">{{trans('base.' . $value)}}</span>
                </li>
            @endforeach
        </ol>
    </div>
</header>
