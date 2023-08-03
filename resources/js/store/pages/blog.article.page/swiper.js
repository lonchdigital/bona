import Swiper from 'swiper/bundle';

export function init () {
    const SwiperArticle = new Swiper('.swiper-article', {
        slidesPerView: "auto",
        slidesPerGroupAuto: true,
        grabCursor: true,
        loop: true,
        navigation: {
            nextEl: '.article-slider .button-slider-next',
            prevEl: '.article-slider .button-slider-prev',
        },
        pagination: {
            el: '.article-slider .swiper-pagination',
            type: 'fraction',
        },
        breakpoints: {
            1200: {},
            768: {
                slidesPerGroup: 1,
                slidesPerGroupAuto: true,
            },
            375: {
            },
        }
    });

    const SwiperArticlePreview = new Swiper('.swiper-article-preview', {
        slidesPerView: 3,
        spaceBetween: 32,
        grabCursor: true,
        breakpoints: {
            768: {
            },
            576: {
                slidesPerView: 2,
            },
            375: {
                slidesPerView: 1.4,
                spaceBetween: 20,
            },
        }
    });
}
