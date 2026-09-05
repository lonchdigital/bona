<button
    class="catalog-menu-drag-handle"
    type="button"
    draggable="true"
    data-menu-drag-handle
    aria-label="{{ $dragLabel }}"
    title="{{ trans('admin.menu_drag_help') }}"
>
    <svg width="16" height="22" viewBox="0 0 16 22" aria-hidden="true">
        <circle cx="4" cy="4" r="1.5"/>
        <circle cx="12" cy="4" r="1.5"/>
        <circle cx="4" cy="11" r="1.5"/>
        <circle cx="12" cy="11" r="1.5"/>
        <circle cx="4" cy="18" r="1.5"/>
        <circle cx="12" cy="18" r="1.5"/>
    </svg>
</button>
