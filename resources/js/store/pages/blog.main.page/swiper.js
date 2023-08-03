import Swiper from 'swiper/bundle';

export function init () {
//? blog-p swiper-blog-banner-top
    const SwiperBlogBannerTop = new Swiper('.blog-p .swiper-blog-banner-top', {
        grabCursor: true,
        slidesPerView: 1.5,
        centeredSlides: true,
        spaceBetween: 32,
        // initialSlide: 1,
        loop: true,
        navigation: {
            nextEl: ".blog-p .blog-banner-top .swiper-buttons .button-slider-next",
            prevEl: ".blog-p .blog-banner-top .swiper-buttons .button-slider-prev",
        },
        pagination: {
            el: ".blog-p .blog-banner-top .swiper-pagination",
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


//? nav-blog-category
    const NavBlogCategory = new Swiper(".nav-blog-category", {
        // direction: "vertical",
        slidesPerView: "auto",
        spaceBetween: 50,
        freeMode: true,
        scrollbar: {
            el: ".swiper-scrollbar",
            // draggable: true,
        },
        mousewheel: true,
    });
}
