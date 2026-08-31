import 'swiper/css';
import Swiper from 'swiper/bundle';


export function init () {
    const popularSlider = document.querySelector('[data-popular-slider]');

    if (popularSlider) {
        new Swiper(popularSlider, {
            slidesPerView: 1.12,
            spaceBetween: 16,
            speed: 450,
            watchOverflow: true,
            grabCursor: true,
            pagination: {
                el: popularSlider.querySelector('.bona-popular__pagination'),
                clickable: true,
            },
            breakpoints: {
                560: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                900: {
                    slidesPerView: 3,
                    spaceBetween: 24,
                },
                1180: {
                    slidesPerView: 4,
                    spaceBetween: 24,
                },
            },
        });
    }

    const instagramSlider = document.querySelector('[data-instagram-slider]');

    if (instagramSlider) {
        const section = instagramSlider.closest('.bona-instagram');

        new Swiper(instagramSlider, {
            slidesPerView: 2,
            spaceBetween: 8,
            speed: 450,
            rewind: true,
            watchOverflow: true,
            grabCursor: true,
            navigation: {
                prevEl: section?.querySelector('.bona-instagram__nav--prev'),
                nextEl: section?.querySelector('.bona-instagram__nav--next'),
            },
            breakpoints: {
                640: {
                    slidesPerView: 3,
                    spaceBetween: 10,
                },
                960: {
                    slidesPerView: 4,
                    spaceBetween: 10,
                },
                1180: {
                    slidesPerView: 6,
                    spaceBetween: 10,
                },
            },
        });
    }

    const reviewsSlider = document.querySelector('[data-reviews-slider]');

    if (reviewsSlider) {
        const section = reviewsSlider.closest('.bona-reviews');

        new Swiper(reviewsSlider, {
            slidesPerView: 1,
            spaceBetween: 16,
            speed: 450,
            rewind: true,
            watchOverflow: true,
            grabCursor: true,
            navigation: {
                prevEl: section?.querySelector('.bona-reviews__nav--prev'),
                nextEl: section?.querySelector('.bona-reviews__nav--next'),
            },
            breakpoints: {
                700: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                1100: {
                    slidesPerView: 3,
                    spaceBetween: 24,
                },
            },
        });
    }


}
