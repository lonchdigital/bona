@extends('layouts.admin-main')
@section('content')
<link rel="stylesheet" href="/static-admin/css/door-configurator.css?v={{ filemtime(public_path('static-admin/css/door-configurator.css')) }}">
<div class="container-fluid cfg-admin" data-configurator-editor>
    <a href="{{ route('admin.configurator.index', ['kind' => $item->kind]) }}">← Конфігуратор · {{ $item->kind === 'door' ? 'Двері' : 'Ручки' }}</a>
    <header class="cfg-header mt-3"><div><h1 class="h3">{{ $item->product?->name ?? 'Товар видалено з каталогу' }}</h1><p>ID {{ $item->product_id ?? '—' }} · {{ $item->published ? 'Є опублікована версія' : 'Приховано на сайті' }} · Редакція {{ $item->version }}</p></div>
        @if($item->product)<a class="btn btn-outline-dark" target="_blank" rel="noopener" href="{{ route('admin.product.edit.page', ['productType' => $item->product->product_type_id, 'product' => $item->product_id]) }}">Товар у каталозі</a>@endif
    </header>
    @include('pages.admin.door-configurator.notices')
    <p>Ціну, назву й доступність змінюйте в товарі. Тут — лише матеріали примірки. <strong>Збереження чернетки не змінює сайт.</strong></p>
    @if($missingColors->isNotEmpty())<div class="alert alert-warning">Нові кольори в каталозі без матеріалів: {{ $missingColors->pluck('name')->implode(', ') }}. Додайте їх кнопкою «Додати відтінок»; автоматично вони не публікуються.</div>@endif
    @if($issues)<details class="cfg-section" open><summary>Що заважає публікації</summary><ul class="mt-2">@foreach($issues as $key => $issue)<li>{{ str_starts_with($key, 'colors.') ? 'Відтінок '.((int) substr($key, 7) + 1).': ' : '' }}{{ $issue }}</li>@endforeach</ul></details>@endif
    <div class="alert alert-danger" id="cfg-client-error" role="alert" hidden></div>
    <form method="post" action="{{ route('admin.configurator.save', $item) }}" id="cfg-edit-form">
        @csrf<input type="hidden" name="version" value="{{ $item->version }}"><input type="hidden" name="payload" id="cfg-payload">
        <div class="cfg-filters"><label>Порядок моделі<input type="number" min="0" max="100000" class="form-control" name="sort_order" value="{{ old('sort_order', $item->sort_order) }}"></label></div>
        <div id="cfg-fields"></div>
        @if($item->kind === 'door')<button type="button" class="btn btn-outline-dark my-3" id="cfg-add-color">Додати відтінок / комплектацію</button>@endif
        <div class="cfg-save-bar"><button class="btn btn-dark" id="cfg-save" disabled>Зберегти чернетку</button><span id="cfg-save-state" role="status">Завантаження редактора…</span></div>
    </form>
    <section class="cfg-section" aria-labelledby="cfg-publish-title"><h2 class="h5" id="cfg-publish-title">Перевірка та публікація</h2><p>Примірка відкриває останню збережену чернетку. Кошик у цьому перегляді вимкнений.</p>
        <div class="cfg-actions">
            <a class="btn btn-outline-dark" href="{{ route('admin.configurator.preview', ['item' => $item, 'locale' => 'uk']) }}" target="_blank" rel="noopener">Примірка UA</a>
            <a class="btn btn-outline-dark" href="{{ route('admin.configurator.preview', ['item' => $item, 'locale' => 'ru']) }}" target="_blank" rel="noopener">Примірка RU</a>
            <form method="post" action="{{ route('admin.configurator.publish', $item) }}">@csrf<input type="hidden" name="version" value="{{ $item->version }}"><button class="btn btn-dark" id="cfg-publish" @disabled(count($issues) > 0 || session()->hasOldInput('payload'))>Опублікувати збережену версію</button></form>
            @if($item->published)<form method="post" action="{{ route('admin.configurator.hide', $item) }}">@csrf<input type="hidden" name="version" value="{{ $item->version }}"><button class="btn btn-outline-danger">Приховати в конфігураторі</button></form>@endif
        </div>
    </section>
    <details class="cfg-section"><summary>Історія змін · останні 30 редакцій</summary><p class="mt-3">Відновлення створює чернетку. Опубліковані матеріали не зміняться до окремої публікації. Попередні фото зберігаються.</p><div class="table-responsive"><table class="table"><thead><tr><th>Редакція</th><th>Дія</th><th>Дата / автор</th><th></th></tr></thead><tbody>
        @foreach($revisions as $revision)<tr><td>{{ $revision->version }}</td><td>{{ ['import'=>'Імпорт', 'save'=>'Чернетка', 'publish'=>'Публікація', 'hide'=>'Приховано', 'restore'=>'Відновлено', 'create'=>'Створено'][$revision->action] ?? $revision->action }}</td><td>{{ $revision->created_at->format('d.m.Y H:i') }} · {{ $revision->user?->first_name ?? 'Система' }}</td><td><form method="post" action="{{ route('admin.configurator.restore', $item) }}">@csrf<input type="hidden" name="version" value="{{ $item->version }}"><input type="hidden" name="revision_id" value="{{ $revision->id }}"><button class="btn btn-sm btn-outline-dark">Відновити як чернетку</button></form></td></tr>@endforeach
    </tbody></table></div></details>
    @php
        $retainedDraft = json_decode(old('payload', 'null'), true);
        $editorData = [
            'kind' => $item->kind, 'draft' => is_array($retainedDraft) ? $retainedDraft : $item->draft,
            'unsaved' => session()->hasOldInput('payload'),
            'colors' => ($item->product?->colors ?? collect())->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'names' => $c->getTranslations('name')])->values(),
            'options' => ($item->product?->attributeOptions ?? collect())->map(fn ($o) => ['id' => $o->id, 'group' => $o->product_attribute_id, 'label' => ($item->product?->productType?->attributes?->firstWhere('id', $o->product_attribute_id)?->attribute_name ?: 'Атрибут #'.$o->product_attribute_id).': '.$o->name])->values(),
            'upload' => route('admin.configurator.upload', $item), 'csrf' => csrf_token(),
        ];
    @endphp
    <script type="application/json" id="cfg-editor-data">{!! json_encode($editorData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
    <noscript><div class="alert alert-warning">Увімкніть JavaScript для редагування матеріалів. Збереження вимкнене, щоб не втратити дані.</div></noscript>
</div>
@endsection
@push('scripts')<script src="/static-admin/js/door-configurator-editor.js?v={{ filemtime(public_path('static-admin/js/door-configurator-editor.js')) }}" defer></script>@endpush
