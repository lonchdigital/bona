<section class="main-header" style="background-image:url({{ asset('storage/bg-images/header-bg.png') }})"></section>
<header class="art-page-header">
    <div class="container">
        <ol class="breadcrumb breadcrumb-inverted font-two">
            <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}"><span class="icon icon-home"></span></a></li>
            @foreach ($links as $url => $value)
                @if($url == '#')
                    <li><span class="{{ $loop->last ? 'active' : '' }}">{{trans('base.' . $value)}}</span></li>
                @elseif($url == 'own')
                    <li><span class="{{ $loop->last ? 'active' : '' }}">{{$value}}</span></li>
                @else
                    <li><a class="{{ $loop->last ? 'active' : '' }}" href="{{ $url }}">{{ $value }}</a></li>
                @endif
            @endforeach
        </ol>
    </div>
</header>
