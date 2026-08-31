import 'swiper/css';
import Swiper from 'swiper/bundle';
import $ from 'jquery';


export function init () {
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


    // new products
    if ($('.art-products-owl-items.art-new-products').length > 0) {
        let NewProductsGallery = new Swiper(".art-products-owl-items.art-new-products", {
            slidesPerView: 4,
            spaceBetween: 30,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                100: {
                    slidesPerView: 1
                },
                500: {
                    slidesPerView: 2
                },
                768: {
                    slidesPerView: 3
                },
                1200: {
                    slidesPerView: 4
                }
            },
            on: {
                init: function () {
                    this.update();
                },
                resize: function () {
                    this.update();
                }
            }
        });
    }

    // best sail products
    if ($('.art-products-owl-items.art-best-products').length > 0) {
        let BestProductsGallery = new Swiper(".art-products-owl-items.art-best-products", {
            slidesPerView: 4,
            spaceBetween: 30,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                100: {
                    slidesPerView: 1
                },
                500: {
                    slidesPerView: 2
                },
                768: {
                    slidesPerView: 3
                },
                1200: {
                    slidesPerView: 4
                }
            },
            on: {
                init: function () {
                    this.update();
                },
                resize: function () {
                    this.update();
                }
            }
        });
    }

}
