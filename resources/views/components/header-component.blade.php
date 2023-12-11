<!-- ======================== Page header ======================== -->
<section class="main-header" style="background-image:url({{ asset('storage/bg-images/header-bg.png') }})"></section>
<header class="art-page-header">
    <div class="container">
        <ol class="breadcrumb breadcrumb-inverted font-two">
            <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}"><span class="icon icon-home"></span></a></li>

            @if($static)
                @foreach ($data as $key => $value)
                    <li><span class="active">{{trans('base.' . $value)}}</span></li>
                @endforeach
            @else
                1111
            @endif
        </ol>
    </div>
</header>
