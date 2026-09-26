@extends('layouts.admin-main')
@section('content')
<link rel="stylesheet" href="/static-admin/css/door-configurator.css?v={{ filemtime(public_path('static-admin/css/door-configurator.css')) }}">
<div class="container-fluid cfg-admin">
    <header class="cfg-header"><div><h1 class="h3">Конфігуратор дверей</h1><p>Підготовлені матеріали для примірки. Назви, ціни й наявність — із каталогу товарів.</p></div><a href="{{ route('store.door-configurator.page') }}" target="_blank" rel="noopener" class="btn btn-outline-dark">Відкрити на сайті <i class="fe fe-external-link" aria-hidden="true"></i></a></header>
    @include('pages.admin.door-configurator.notices')
    @unless($managed)<div class="alert alert-warning">Поки сайт використовує файлову версію. Виконайте початковий імпорт нижче, щоб увімкнути керування через адмінку.</div>@endunless
    <nav class="catalog-menu-tabs" aria-label="Матеріали конфігуратора">
        @foreach(['door' => 'Двері', 'handle' => 'Ручки'] as $tab => $label)<a class="catalog-menu-tabs__item {{ $kind === $tab ? 'is-active' : '' }}" href="{{ route('admin.configurator.index', ['kind' => $tab]) }}" @if($kind === $tab) aria-current="page" @endif>{{ $label }}</a>@endforeach
    </nav>
    <form method="get" class="cfg-filters">
        <input type="hidden" name="kind" value="{{ $kind }}">
        <label>Назва або ID<input class="form-control" name="q" value="{{ request('q') }}" placeholder="Знайти модель"></label>
        @if($kind === 'door')<label>Категорія<select class="form-control" name="category">@foreach(['' => 'Усі категорії', 'interior' => 'Міжкімнатні', 'hidden' => 'Приховані', 'mirror' => 'Дзеркальні', 'classic' => 'Класичні', 'exterior' => 'Вхідні'] as $value => $label)<option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>@endforeach</select></label>@endif
        <label>Стан<select class="form-control" name="status">@foreach(['' => 'Усі матеріали', 'published' => 'Опубліковано', 'draft' => 'Чернетки / зміни', 'missing' => 'Потрібні зображення'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>
        <button class="btn btn-dark" type="submit">Знайти</button><a href="{{ route('admin.configurator.index', ['kind' => $kind]) }}">Скинути</a>
    </form>
    <p>{{ $paginator->total() }} моделей за вибраними умовами</p>
    <div class="table-responsive cfg-table"><table class="table table-hover"><thead><tr><th>Модель</th><th>Відтінки на сайті</th><th>Стан</th><th>Оновлено</th><th><span class="sr-only">Дії</span></th></tr></thead><tbody>
        @forelse($paginator as $item)
            @php
                $image = $item->kind === 'door' ? ($item->draft['colors'][0]['image'] ?? null) : ($item->draft['image'] ?? null);
                $publishedCount = $item->kind === 'door' ? count($item->published['colors'] ?? []) : ($item->published ? 1 : 0);
                $changed = $item->published && $item->published !== app(\App\Services\Product\ConfiguratorCatalog::class)->publishable($item->draft);
            @endphp
            <tr><td><div class="cfg-model">@if($image)<img src="{{ $image }}" alt="" width="56" height="72" loading="lazy">@else<span class="cfg-no-photo">Немає<br>фото</span>@endif<div><a href="{{ route('admin.configurator.edit', $item) }}"><strong>{{ $item->product?->name ?? 'Товар видалено' }}</strong></a><small>ID {{ $item->product_id ?? '—' }} · {{ $item->product?->brand?->name ?? 'Без виробника' }}</small></div></div></td>
                <td>{{ $publishedCount }} <small class="d-block">{{ $item->product?->colors?->count() ?? 0 }} кольорів у каталозі</small></td>
                <td>{{ $item->published ? 'Опубліковано' : 'Приховано / чернетка' }}@if($changed)<small class="d-block text-warning">Є неопубліковані зміни</small>@endif</td>
                <td>{{ $item->updated_at->format('d.m.Y H:i') }}</td><td><a class="btn btn-sm btn-outline-dark" href="{{ route('admin.configurator.edit', $item) }}">Редагувати</a></td>
            </tr>
        @empty<tr><td colspan="5">Нічого не знайдено. Змініть фільтри або додайте модель із каталогу нижче.</td></tr>@endforelse
    </tbody></table></div>
    @if($paginator->hasPages())<nav class="cfg-actions my-4" aria-label="Сторінки моделей">@if($paginator->previousPageUrl())<a class="btn btn-outline-dark" href="{{ $paginator->previousPageUrl() }}">Попередня</a>@endif<span>Сторінка {{ $paginator->currentPage() }} з {{ $paginator->lastPage() }}</span>@if($paginator->nextPageUrl())<a class="btn btn-outline-dark" href="{{ $paginator->nextPageUrl() }}">Наступна</a>@endif</nav>@endif
    <section class="cfg-section" aria-labelledby="cfg-add-title"><h2 class="h5" id="cfg-add-title">Додати з каталогу</h2><p>Товар не дублюється. Створиться прихована чернетка для завантаження матеріалів.</p>
        <form method="get" class="cfg-filters"><input type="hidden" name="kind" value="{{ $kind }}"><label>Назва або ID товару<input class="form-control" name="catalog_search" value="{{ request('catalog_search') }}" minlength="2" required></label><button class="btn btn-outline-dark">Пошук у каталозі</button></form>
        @if(request('catalog_search'))
            @if($catalogProducts->isEmpty())<p>Нових товарів за цим запитом не знайдено. Уже додані моделі шукайте у верхній таблиці.</p>@else
            <form method="post" action="{{ route('admin.configurator.create') }}" class="cfg-filters">@csrf<input type="hidden" name="kind" value="{{ $kind }}">
                <label>Товар<select class="form-control" name="product_id" required>@foreach($catalogProducts as $product)<option value="{{ $product->id }}">#{{ $product->id }} — {{ $product->name }} ({{ $product->productType?->name }})</option>@endforeach</select></label>
                @if($kind === 'door')<label>Категорія<select class="form-control" name="category"><option value="interior">Міжкімнатні</option><option value="exterior">Вхідні</option></select></label>@endif
                <button class="btn btn-dark">Створити чернетку</button>
            </form>@endif
        @endif
    </section>
    <details class="cfg-section" @if(!$managed || session('import_report')) open @endif><summary>Імпорт готових матеріалів</summary><p class="mt-3">Імпортує ще не додані моделі з підготовленого набору. Уже додані моделі не змінює. Фото зберігаються окремо від релізів, без дублів за вмістом.</p>
        <form method="post" action="{{ route('admin.configurator.import') }}" class="cfg-actions">@csrf<button class="btn btn-outline-dark" name="mode" value="check">Перевірити імпорт</button><button class="btn btn-dark" name="mode" value="apply">Імпортувати готові моделі</button></form>
        @if($report = session('import_report'))<p class="mt-3">Нових: {{ $report['created'] }} · Залишено без змін: {{ $report['preserved'] }} · Опубліковано: {{ $report['published'] }}</p>@if($report['warnings'])<details><summary>Потребують уваги ({{ count($report['warnings']) }})</summary><ul>@foreach($report['warnings'] as $warning)<li>{{ $warning }}</li>@endforeach</ul></details>@endif @endif
    </details>
    <p class="cfg-note">Простори й палітра стін залишаються поточними. Тут редагуються лише двері, їхні відтінки та ручки.</p>
</div>
@endsection
