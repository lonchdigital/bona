import Swiper from 'swiper/bundle';

export function init () {
    //? popular-city swiper
    const NavBlogCategory = new Swiper(".popular-city", {
        // direction: "vertical",
        slidesPerView: "auto",
        spaceBetween: 30,
        freeMode: true,
        grabCursor: true,
        scrollbar: {
            el: ".popular-city .swiper-scrollbar",
            // draggable: true,
        },
        mousewheel: true,
    });
}
