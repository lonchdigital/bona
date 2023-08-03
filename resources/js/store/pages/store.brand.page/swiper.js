import Swiper from 'swiper/bundle';

export function init () {
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
    });

    const SwiperSingleBrand = new Swiper(".single-brand-slider .swiper-single-brand", {
        // slidesPerView: 'auto',
        slidesPerView: 'auto',
        spaceBetween: 40,
        grabCursor: true,
        loop: true,
        centeredSlides: true,
        navigation: {
            nextEl: '.single-brand-slider .button-slider-next',
            prevEl: '.single-brand-slider .button-slider-prev',
        },
        pagination: {
            el: '.single-brand-slider .swiper-pagination',
            type: 'fraction',
        },
        breakpoints: {
            992: {},
            576: {
                centeredSlides: false,
                spaceBetween: 16,
            },
            375: {
                centeredSlides: false,
                slidesPerView: 1.6,
                spaceBetween: 12,
            },

        },
    });

}
