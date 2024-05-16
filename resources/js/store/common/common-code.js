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

    }


};
