import Swiper from 'swiper/bundle';

export function init () {
    const SwiperBlogBannerTop = new Swiper('.collection-p .swiper-blog-banner-top', {
        grabCursor: true,
        slidesPerView: 1.5,
        centeredSlides: true,
        spaceBetween: 32,
        // initialSlide: 1,
        loop: true,
        navigation: {
            nextEl: ".collection-p .blog-banner-top .swiper-buttons .button-slider-next",
            prevEl: ".collection-p .blog-banner-top .swiper-buttons .button-slider-prev",
        },
        pagination: {
            el: ".collection-p .blog-banner-top .swiper-pagination",
            clickable: true,
        },
        breakpoints: {
            992: {},
            768: {
                spaceBetween: 16,
            },
            375: {
                spaceBetween: 10,
                slidesPerView: 1.1,
            }
        }
    });
}
