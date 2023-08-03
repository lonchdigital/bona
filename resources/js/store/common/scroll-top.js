import $ from "jquery";

let bgEnabled = false;

export default {
    init: async function () {
        $(window).scroll(function () {
            const btnArrowUp = $('.btn-arrow-up')
            if ($(document).scrollTop() > 500 && btnArrowUp.length) {
                btnArrowUp.addClass('visible');
            } else {
                btnArrowUp.removeClass('visible');
            }
        }).scroll();

        handleScrollTop();

        $(window).on('resize', function () {
            if (!bgEnabled) {
                handleScrollTop();
            }
        })
    }
}

function handleScrollTop()
{
    const headerMain = $(".header-main");

    if (window.innerWidth < 992 && headerMain.length) {
        $(window).scrollTop() <= 70 ? headerMain.addClass("header-main-bg") : $(".header-main-mob").removeClass("header-main-bg");

        $(window).scroll(function () {
            $(window).scrollTop() <= 70 ? headerMain.addClass("header-main-bg") : $(".header-main-mob").removeClass("header-main-bg");
        });

        let lastScrollTop = 0;
        $(window).scroll(function (event) {
            let st = $(this).scrollTop();
            if (st > lastScrollTop && $(window).scrollTop() >= 70) {
                headerMain.addClass("header-main-hide")
            } else {
                headerMain.removeClass("header-main-hide").addClass("header-main-bg");
            }
            lastScrollTop = st;
        });

        bgEnabled = true;
    }
}
