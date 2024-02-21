// import Swiper, { Navigation, Pagination } from 'swiper';
import Swiper from 'swiper/bundle';
import $ from "jquery";

export function init () {
//? swiper-single-wallpaper-thumbs

    const swiperSingleWallpaperThumbsWrap = document.querySelector('.swiper-single-wallpaper-thumbs-wrap');
    let imagesCount = swiperSingleWallpaperThumbsWrap.querySelectorAll('img').length;

    imagesCount = imagesCount >= 4 ? 4 : imagesCount;

    console.log(imagesCount);
    if(imagesCount < 4) {
        $('.swiper-single-wallpaper-thumbs').find('.swiper-wrapper').addClass('art-few-thumbs');
    }

    const SwiperSingleWallpaperThumbs = new Swiper('.swiper-single-wallpaper-thumbs', {
        // slidesPerView: imagesCount,
        // slidesPerView: 4,
        // slidesPerGroupAuto: false,
        // slidesPerGroup: 1,
        spaceBetween: 0,
        freeMode: true,
        pagination: {
            //enabled: true,
            el: ".swiper-single-wallpaper-thumbs-wrap .swiper-pagination",
            clickable: true
        },
        breakpoints: {
            300: {
                slidesPerView: 2
                // spaceBetween: 20
            },
            768: {
                slidesPerView: 3
                // spaceBetween: 50
            },
            1200: {
                // slidesPerView: imagesCount,
                slidesPerView: 4,
                spaceBetween: calculateSpaceBetween()
            }
        },
        on: {
            init: function () {
                // При инициализации Swiper
                this.params.spaceBetween = calculateSpaceBetween();
                this.update(); // Обновление Swiper
            },
            resize: function () {
                // При изменении размеров окна
                // this.params.spaceBetween = calculateSpaceBetween();
                this.update(); // Обновление Swiper
            }
        }
    });

    /*window.addEventListener('resize', () => {
        SwiperSingleWallpaperThumbs.update(); // Обновление Swiper при изменении размера окна
    });*/
    function calculateSpaceBetween() {
        let spaceBetween = 0;
        if(imagesCount === 3) {
            spaceBetween = 20;
        } else if(imagesCount === 2) {
            spaceBetween = 20;
        }

        return spaceBetween;
    }


//? swiper-single-wallpaper
    const SwiperSingleWallpaper = new Swiper('.swiper-single-wallpaper', {
        loop: true,
        slidesPerView: 1,
        // simulateTouch: 0,
        thumbs: {
            swiper: SwiperSingleWallpaperThumbs
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev"
        },
        on: {
            slideChange: function (event) {
                $('#fancybox-trigger').attr('href', '#single-wallpaper-gallery-' + (event.activeIndex + 1));
            }
        }
    });



    //? swiper-cards-products
    /*const SwiperCardsProducts = new Swiper('.swiper-cards-products', {
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
        on: {
            init: function () {
                showProductsFromSameCollectionSwiper();
            }
        }
    });*/
}

function showGallerySwiper()
{

    const wrap = $('.swiper-single-wallpaper-thumbs-wrap');
    const inner = wrap.find('.inner');
    const body = wrap.find('.swiper-single-wallpaper-thumbs');
    const prev = wrap.find('.button-slider-prev');
    const next = wrap.find('.button-slider-prev');

    inner.removeClass('loading-skeleton');
    body.removeClass('invisible');
    prev.removeClass('invisible');
    next.removeClass('invisible');

}

function showProductsFromSameCollectionSwiper()
{
    const body = $('.cards-products');
    const list = $('.swiper-cards-products');
    const wrapper = body.find('.swiper-wrapper');
    const control = body.find('.swiper-control');

    list.removeClass('loading-skeleton');
    wrapper.removeClass('invisible');
    control.removeClass('invisible');
}
