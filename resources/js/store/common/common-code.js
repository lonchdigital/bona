import $ from "jquery";

export default {

    init: async function () {

        // Display Filters on Products Catalog by Click
        // ----------------------------------------------------------------
        var $productsFilter = $('#art-products-filter');
        var $filterDisplay = $('#art-filter-display, #art-filter-display *');

        $filterDisplay.on('click', function (e) {
            var h_this = $(this);

            if(!h_this.hasClass('active')) {
                $productsFilter.addClass('active');
            }

        });

        $('#art-products-filter .filter-top-wrapper svg').on('click', function (e) {
            $productsFilter.removeClass('active');
        });

        $(document).on('click', function(e) {
            if (!$productsFilter.is(e.target) && $productsFilter.has(e.target).length === 0 && !$filterDisplay.is(e.target)) {
                $productsFilter.removeClass('active');
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
