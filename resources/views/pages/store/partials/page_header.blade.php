<section class="main-header" style="background-image:url({{ asset('storage/bg-images/header-bg.png') }})"></section>
<header class="art-page-header">
    <div class="container">
        <ol class="breadcrumb breadcrumb-inverted font-two" itemscope itemtype="https://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}"><span class="icon icon-home"></span></a>
            </li>
            @foreach ($links as $url => $value)
                @if($url == '#')
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span class="{{ $loop->last ? 'active' : '' }}" itemprop="name">{{trans('base.' . $value)}}</span></li>
                @elseif($url == 'own')
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span class="{{ $loop->last ? 'active' : '' }}" itemprop="name">{{$value}}</span></li>
                @elseif($url == 'own-2')
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><span class="{{ $loop->last ? 'active' : '' }}" itemprop="name">{{$value}}</span></li>
                @else
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a class="{{ $loop->last ? 'active' : '' }}" href="{{ $url }}" itemprop="name">{{ $value }}</a></li>
                @endif
            @endforeach
        </ol>
    </div>
</header>
