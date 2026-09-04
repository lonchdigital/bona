@props(['items' => []])

<nav class="bona-content-breadcrumbs bona-shell" aria-label="{{ trans('base.breadcrumbs') }}">
    <ol>
        <li>
            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}">
                {{ trans('base.home') }}
            </a>
        </li>

        @foreach($items as $item)
            <li aria-hidden="true">/</li>
            <li @if($loop->last) aria-current="page" @endif>
                @if(! $loop->last && filled($item['url'] ?? null))
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    {{ $item['label'] }}
                @endif
            </li>
        @endforeach
    </ol>
</nav>
