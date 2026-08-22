<section class="main-header" style="background-image:url({{ asset('storage/bg-images/header-bg.png') }})"></section>
<header class="art-page-header">
    <div class="container">
        {{--
            The trail carried BreadcrumbList microdata without a position or an
            item on any entry, which is what search engines need to read it, so
            it was ignored. Each entry now states its position and, where it
            leads somewhere, what it points at.
        --}}
        @php
            $breadcrumbPosition = 1;
        @endphp

        <ol class="breadcrumb breadcrumb-inverted font-two" itemscope itemtype="https://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a itemprop="item" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}">
                    <span class="icon icon-home"></span>
                    <span itemprop="name" class="sr-only">{{ trans('base.home') }}</span>
                </a>
                <meta itemprop="position" content="{{ $breadcrumbPosition++ }}">
            </li>

            @foreach ($links as $url => $value)
                @if(in_array($url, ['#', 'own', 'own-2'], true))
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <span class="{{ $loop->last ? 'active' : '' }}" itemprop="name">{{ $url === '#' ? trans('base.' . $value) : $value }}</span>
                        <meta itemprop="position" content="{{ $breadcrumbPosition++ }}">
                    </li>
                @else
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a class="{{ $loop->last ? 'active' : '' }}" itemprop="item" href="{{ $url }}">
                            <span itemprop="name">{{ $value }}</span>
                        </a>
                        <meta itemprop="position" content="{{ $breadcrumbPosition++ }}">
                    </li>
                @endif
            @endforeach
        </ol>
    </div>
</header>
