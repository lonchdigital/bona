import $ from "jquery";

import 'swiper/css';
import Swiper from 'swiper/bundle';


export function init () {

    // home slider
    const SwiperSingleWallpaper = new Swiper('.swiper.owl-slider', {
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
            pauseOnMouseEnter: false
        },
        slidesPerView: 1,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        }
    });


    $(document).ready(function () {

        function updateBackgroundImages() {
            $('.home-page-slide-itself').each(function () {
                let windowWidth = $(window).width();

                let pcImage = $(this).data('pc');
                let mobImage = $(this).data('mob');

                let selectedImage = windowWidth > 767 ? pcImage : mobImage;

                $(this).css('background-image', `url(${selectedImage})`);
            });
        }

        updateBackgroundImages();

        $(window).resize(function () {
            updateBackgroundImages();
        });
    });


    // home slider icons
    let HomeSliderIcons= new Swiper(".swiper.owl-icons", {
        loop: true,
        slidesPerView: 6,
        spaceBetween: 30,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev"
        },
        breakpoints: {
            100: {
                slidesPerView: 2
            },
            500: {
                slidesPerView: 3
            },
            768: {
                slidesPerView: 4
            },
            1200: {
                slidesPerView: 6
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

    // instagram
    if ($('#art-instagram-owl-items.art-instagram').length > 0) {
        let NewProductsGallery = new Swiper("#art-instagram-owl-items.art-instagram", {
            slidesPerView: 4,
            spaceBetween: 30,
            loop: true,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
                dynamicBullets: true,
                dynamicMainBullets: 6
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev"
            },
            breakpoints: {
                100: {
                    slidesPerView: 3
                },
                500: {
                    slidesPerView: 4
                },
                768: {
                    slidesPerView: 5
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

    // testimonials
    if ($('.art-quote-carousel-home.quote-carousel').length > 0) {
        let NewProductsGallery = new Swiper(".art-quote-carousel-home.quote-carousel", {
            slidesPerView: 3,
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
                1200: {
                    slidesPerView: 3
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

    // Our partners
    if ($('.swiper.art-brands-owl-items').length > 0) {
        let NewProductsGallery = new Swiper(".swiper.art-brands-owl-items", {
            slidesPerView: 5,
            spaceBetween: 30,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                100: {
                    slidesPerView: 2
                },
                500: {
                    slidesPerView: 5
                },
                768: {
                    slidesPerView: 4
                },
                1200: {
                    slidesPerView: 5
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
