import $ from "jquery";

export function init () {
    $('#pagination-previous-page').click(function (event) {
        event.preventDefault();

        if (catalog.current_page > 1) {
            $(window).trigger('changePage', catalog.current_page - 1);
        }
    });

    $('#pagination-next-page').click(function (event) {
        event.preventDefault();

        if (catalog.current_page < catalog.last_page) {
            $(window).trigger('changePage', catalog.current_page + 1);
        }
    });

    $('.page-link-clickable').click(function (event) {
        event.preventDefault();
        if ($(this).attr('href')) {
            const page = $(this).attr('href').substring(1);

            if(!isNaN(page)) {
                $(window).trigger('changePage', page);
            }
        }
    })
}


