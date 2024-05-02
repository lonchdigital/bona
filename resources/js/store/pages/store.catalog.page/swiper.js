import $ from "jquery";
import Swiper from 'swiper/bundle';

export function init () {
    const SwiperCatalogCategory = new Swiper('.swiper-catalog-category', {
        slidesPerView: 6,
        spaceBetween: 32,
        navigation: {
            nextEl: '.catalog-category-slider .button-slider-next',
            prevEl: '.catalog-category-slider .button-slider-prev',
        },
        breakpoints: {
            1400: {},
            992: {
                slidesPerView: 4,
            },
            768: {
                slidesPerView: 3,
            },
            576: {
                slidesPerView: 2,
            },
            375: {
                slidesPerView: 2.7,
            },
        }
    });

    if (window.innerWidth < 992) {
        $('.filter-views').addClass('swiper-container');
        $('.filter-views-content').addClass('swiper-wrapper');
        $('.filter-view-item').addClass('swiper-slide');
        const FilterViews = new Swiper('.filter-views', {
            slidesPerView: "auto",
            spaceBetween: 8,
            freeMode: true,
            grabCursor: true,
            scrollbar: {
                el: ".swiper-scrollbar",
            },
            mousewheel: true,
        });
    }

    $('#category-swiper-inner').removeClass('loading-skeleton').removeClass('rounded');
    $('#category-swiper-body').removeClass('invisible');
    $('#category-swiper-controls').removeClass('invisible');
}
