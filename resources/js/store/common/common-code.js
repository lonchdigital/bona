import $ from "jquery";

export default {

    init: async function () {

        // Display Filters on Products Catalog by Click
        // ----------------------------------------------------------------
        var $productsFilter = $('#art-products-filter');
        var $filterDisplay = $('#art-filter-display');

        $filterDisplay.on('click', function (e) {
            e.preventDefault();
            $productsFilter.addClass('active');
            $filterDisplay.addClass('active').attr('aria-expanded', 'true');
            $('body').addClass('bona-filter-open');

        });

        $('#art-products-filter .filter-top-wrapper button, #art-products-filter .filter-top-wrapper > svg').on('click', function (e) {
            $productsFilter.removeClass('active');
            $filterDisplay.removeClass('active').attr('aria-expanded', 'false');
            $('body').removeClass('bona-filter-open');
        });

        $(document).on('click', function(e) {
            if (!$productsFilter.is(e.target) && $productsFilter.has(e.target).length === 0 && !$filterDisplay.is(e.target) && $filterDisplay.has(e.target).length === 0) {
                $productsFilter.removeClass('active');
                $filterDisplay.removeClass('active').attr('aria-expanded', 'false');
                $('body').removeClass('bona-filter-open');
            }
        });

        $(document).on('keydown', function (event) {
            if (event.key === 'Escape' && $productsFilter.hasClass('active')) {
                $productsFilter.removeClass('active');
                $filterDisplay.removeClass('active').attr('aria-expanded', 'false').trigger('focus');
                $('body').removeClass('bona-filter-open');
            }
        });


        // Show / hide Colors block by click
        // ----------------------------------------------------------------
        let artFilterColorContent = $('#art-filter-color-content');
        let artShowColorsButton = $('#art-filter-color-control .art-show-colors');
        let artHideColorsButton = $('#art-filter-color-control .art-hide-colors');

        artShowColorsButton.on('click', function (e) {
            $(this).removeClass('d-block').addClass('d-none');
            artHideColorsButton.removeClass('d-none').addClass('d-block');

            artFilterColorContent.addClass('content-expanded');
        });
        artHideColorsButton.on('click', function (e) {
            $(this).removeClass('d-block').addClass('d-none');
            artShowColorsButton.removeClass('d-none').addClass('d-block');

            artFilterColorContent.removeClass('content-expanded');
        });

    }

};
