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


        $(document).ready(function() {

            // click on name = click on label
            $('.color-name').on('click', function() {
                $(this).prev('label').click();
            });



            // TODO:: remove old dialog if do not need

            //  dialog-language start
            /*var hasCodeBeenExecuted = sessionStorage.getItem("codeExecuted");
            if (!hasCodeBeenExecuted) {
                var button = document.getElementById("art-language-popup-button");
                button.click();

                sessionStorage.setItem("codeExecuted", "true");
            }*/
            //  dialog-language end

            //  dialog-language start
            var storedData = localStorage.getItem("codeExecuted");
            var currentTime = new Date().getTime();

            if (!storedData || currentTime - JSON.parse(storedData).timestamp > 24 * 60 * 60 * 1000) {
                var button = document.getElementById("art-language-popup-button");
                button.click();


                localStorage.setItem(
                    "codeExecuted",
                    JSON.stringify({ timestamp: currentTime })
                );
            }
            //  dialog-language end

        });



    }

};
