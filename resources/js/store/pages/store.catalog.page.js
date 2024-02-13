import $ from 'jquery';

export default async function () {

    const customLabels = $('.filter-item--type-custom .custom-control-label');
    const countriesLabels = $('.filter-item--countries .custom-control-label');
    const searchableLabels = $('.filter-item--brands .custom-control-label');
    const colorLabels = $('.filter-item--colors .link-color');

    handleCheckBoxes(customLabels);
    handleCheckBoxes(countriesLabels);
    handleCheckBoxes(searchableLabels);
    handleCheckBoxes(colorLabels, function (element, isChecked) {
        if (isChecked) {
            element.addClass('active');
        } else {
            element.removeClass('active');
        }
    });

    handleShowMoreButton();

    const [
        // SvelteRangeSlider,
        Tooltip,
        SyncFilters,
        SearchableList,
        FilterSubmit,
        Swiper,
        Pagination,
    ] = await Promise.all([
        // import('./store.catalog.page/svelte-range-slider'),
        import('./store.catalog.page/tooltip'),
        import('./store.catalog.page/sync-filters'),
        import('./store.catalog.page/searchable-list'),
        import('./store.catalog.page/filter-submit'),
        import('./store.catalog.page/swiper'),
        import('./store.catalog.page/pagination')
    ]);

    Swiper.init();
    // SvelteRangeSlider.init();
    Tooltip.init();
    SyncFilters.init();
    SearchableList.init();
    FilterSubmit.init();
    Pagination.init();
}

async function handleCheckBoxes(label, callback)
{
    label.click(function (event, isSync) {

        event.preventDefault();

        const checkBox = $('#' + $(this).attr('for'));
        const parent = $(this).parent();

        if (!checkBox.is(':checked')) {
            parent.addClass('checked');
            checkBox.prop("checked", true);
        } else {
            parent.removeClass('checked');
            checkBox.prop("checked", false);
        }

        if (!isSync) {
            checkBox.trigger('change');
        }

        if (callback) {
            callback($(this), checkBox.is(':checked'));
        }
    });
}

async function handleShowMoreButton()
{
    $(".btn-show-more").click(function () {
        if ($(this).closest(".filter-item").find(".content-checkoxs").hasClass("show-more")) {
            $(this).text(translations.filter_show_less);
        } else {
            $(this).text(translations.filter_show_more);
        }

        $(this).closest(".filter-item").find(".content-checkoxs").toggleClass("show-more");
    });

    if (window.innerWidth > 991) {
        $(function () {
            $('.filter-views .filter-views-content').each(function () {
                let max = 10;
                let items = $(this).find('.filter-view-item'),
                    len = items.length;
                if (len > max) {
                    items = items.slice(max, len);
                    items.wrapAll('<div class="hide"></div>');
                    $(this).append('<div class="btn-show-more">' + translations.filter_show_more + '</div>');
                }

            })
        }).on('click', '.filter-views .filter-views-content .btn-show-more', function () {
            $(this).closest('.filter-views .filter-views-content').toggleClass("show-more").find('.hide > .filter-view-item').unwrap();
            if ($(".filter-views .filter-views-content").hasClass("show-more")) {
                $(this).text(translations.filter_show_less);
            } else {
                $(this).text(translations.filter_show_more);
                let max = 10;
                $('.filter-views .filter-views-content').each(function () {
                    let items = $(this).find('.filter-view-item'),
                        len = items.length;
                    if (len > max) {
                        items = items.slice(max, len);
                        items.wrapAll('<div class="hide"></div>');
                    }
                })
            }
        });
    }
}
