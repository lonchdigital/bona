import $ from 'jquery';
import Swiper from 'swiper/bundle';

export default async function () {
    const [
        EmailSubscription,
    ] = await Promise.all([
        import('../common/email-subscription'),

    ]);

    EmailSubscription.default.init();

    const SwiperWillNeed = new Swiper('.swiper-will-need', {
        slidesPerView: 4,
        spaceBetween: 64,
        grabCursor: true,
        navigation: {
            nextEl: ".swiper-will-need .swiper-buttons .button-slider-next",
            prevEl: ".swiper-will-need .swiper-buttons .button-slider-prev",
        },
        pagination: {
            el: ".swiper-will-need .swiper-pagination",
            clickable: true,
        },
        breakpoints: {
            1400: {},
            1200: {
                spaceBetween: 32,
            },
            992: {
                spaceBetween: 32,
                slidesPerView: 3,
            },
            768: {
                spaceBetween: 20,
                slidesPerView: 3.5,
            },
            576: {
                spaceBetween: 20,
                slidesPerView: 2.5,
                slidesPerGroup: 2,
            },
            375: {
                spaceBetween: 20,
                slidesPerView: 2.2,
                slidesPerGroup: 2,
            },
        }
    });
}
