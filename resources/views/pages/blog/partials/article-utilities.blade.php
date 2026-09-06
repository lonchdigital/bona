<aside class="bona-article-sidebar" aria-label="{{ $isRussian ? 'Полезные действия' : 'Корисні дії' }}">
    <div class="bona-article-sidebar__sticky">
        <article class="bona-article-consultant">
            <div class="bona-article-consultant__top">
                <span>{{ trans('base.catalog_help_kicker') }}</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/><path d="M8 10h8M8 14h5"/></svg>
            </div>
            <div class="bona-article-consultant__person">
                <img src="{{ Vite::asset('bona-html/img/manager-oksana.webp') }}" alt="{{ trans('base.catalog_consultant_photo_alt') }}" width="72" height="72" loading="lazy" decoding="async">
                <span><strong>{{ trans('base.catalog_consultant_name') }}</strong><small>{{ trans('base.catalog_consultant_role') }}</small></span>
            </div>
            <h2>{{ trans('base.catalog_consultant_title') }}</h2>
            <p>{{ trans('base.catalog_consultant_text') }}</p>
            <a href="#dialog-call-consultation" data-lead-modal-open="dialog-call-consultation">{{ trans('base.catalog_get_consultation') }}<span aria-hidden="true">→</span></a>
        </article>

        <article class="bona-article-configurator">
            <figure class="bona-article-configurator__media">
                <img src="{{ Vite::asset('bona-html/img/interior-bedroom.jpg') }}" alt="{{ $isRussian ? 'Двери в современном интерьере' : 'Двері в сучасному інтер’єрі' }}" width="540" height="320" loading="lazy" decoding="async">
            </figure>
            <div class="bona-article-configurator__copy">
                <span>{{ $isRussian ? 'Конфигуратор дверей' : 'Конфігуратор дверей' }}</span>
                <h2>{{ $isRussian ? 'Увидьте двери в своём интерьере' : 'Побачте двері у своєму інтер’єрі' }}</h2>
                <p>{{ $isRussian ? 'Выберите стиль, цвет и бюджет — покажем подходящие модели.' : 'Оберіть стиль, колір і бюджет — покажемо відповідні моделі.' }}</p>
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.door-configurator.page') }}">{{ $isRussian ? 'Подобрать двери' : 'Підібрати двері' }}<span aria-hidden="true">→</span></a>
            </div>
        </article>
    </div>
</aside>
