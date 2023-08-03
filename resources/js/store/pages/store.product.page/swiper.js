import Swiper, { Navigation, Pagination } from 'swiper';
import $ from "jquery";

export function init () {
//? swiper-single-wallpaper-thumbs
    const SwiperSingleWallpaperThumbs = new Swiper('.swiper-single-wallpaper-thumbs', {
        direction: "vertical",
        slidesPerView: 'auto',
        slidesPerGroupAuto: false,
        slidesPerGroup: 1,
        spaceBetween: 2,
        //watchSlidesProgress: true,
        navigation: {
            //enabled: true,
            // nextEl: ".swiper-single-wallpaper-thumbs-wrap .button-slider-next",
            // prevEl: ".swiper-single-wallpaper-thumbs-wrap .button-slider-prev",
        },
        pagination: {
            //enabled: true,
            el: ".swiper-single-wallpaper-thumbs-wrap .swiper-pagination",
            //clickable: true,
        },
    });

    //SwiperSingleWallpaperThumbs.lockSwipeToNext();

//? swiper-single-wallpaper
    const SwiperSingleWallpaper = new Swiper('.swiper-single-wallpaper', {
        loop: true,
        slidesPerView: 1,
        // simulateTouch: 0,
        thumbs: {
            swiper: SwiperSingleWallpaperThumbs,
        },
        pagination: {
            el: ".swiper-single-wallpaper-wrap .swiper-pagination",
            clickable: true,
        },
        on: {
            slideChange: function (event) {
                $('#fancybox-trigger').attr('href', '#single-wallpaper-gallery-' + (event.activeIndex + 1));
            }
        }
    });


    $('.swiper-single-wallpaper-thumbs-wrap .button-slider-next').click(function (event) {
        event.preventDefault();
        SwiperSingleWallpaper.slideNext();
    });

    $('.swiper-single-wallpaper-thumbs-wrap .button-slider-prev').click(function (event) {
        event.preventDefault();
        SwiperSingleWallpaper.slidePrev();
    });

    //? swiper-cards-products
    const SwiperCardsProducts = new Swiper('.swiper-cards-products', {
        spaceBetween: 16,
        navigation: {
            nextEl: ".product-collection-slider .swiper-buttons .button-slider-next",
            prevEl: ".product-collection-slider .swiper-buttons .button-slider-prev",
        },
        pagination: {
            el: ".product-collection-slider .swiper-pagination",
            clickable: true,
        },
        breakpoints: {
            1200: {
                slidesPerView: 4,
                spaceBetween: 32,
            },
            768: {
                slidesPerView: 3,
            },
            375: {
                spaceBetween: 20,
                slidesPerView: 2,
                grid: {
                    rows: 2,
                    fill: "row",
                },
            }
        },
        on: {
            init: function () {
                showProductsFromSameCollectionSwiper();
            }
        }
    });
}

function showGallerySwiper()
{

    const wrap = $('.swiper-single-wallpaper-thumbs-wrap');
    const inner = wrap.find('.inner');
    const body = wrap.find('.swiper-single-wallpaper-thumbs')
    const prev = wrap.find('.button-slider-prev');
    const next = wrap.find('.button-slider-prev');

    inner.removeClass('loading-skeleton');
    body.removeClass('invisible');
    prev.removeClass('invisible');
    next.removeClass('invisible');

}

function showProductsFromSameCollectionSwiper()
{
    const body = $('.cards-products');
    const list = $('.swiper-cards-products');
    const wrapper = body.find('.swiper-wrapper');
    const control = body.find('.swiper-control');

    list.removeClass('loading-skeleton');
    wrapper.removeClass('invisible');
    control.removeClass('invisible');
}
