import $ from "jquery";

import 'swiper/css';
import Swiper from 'swiper/bundle';


export function init () {

    // Single Product Gallery
    const swiperSingleWallpaperThumbsWrap = document.querySelector('.swiper-single-wallpaper-thumbs-wrap');

    let SwiperSingleWallpaperThumbs = '';
    if( swiperSingleWallpaperThumbsWrap !== null ) {

        let areThumbs = swiperSingleWallpaperThumbsWrap.querySelectorAll('img');


        if ( areThumbs !== null ) {
            let imagesCount = swiperSingleWallpaperThumbsWrap.querySelectorAll('img').length;
            imagesCount = imagesCount >= 4 ? 4 : imagesCount;
            if(imagesCount < 4) {
                $('.swiper-single-wallpaper-thumbs').find('.swiper-wrapper').addClass('art-few-thumbs');
            }

            SwiperSingleWallpaperThumbs = new Swiper('.swiper-single-wallpaper-thumbs', {
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
                    },
                    768: {
                        slidesPerView: 3
                    },
                    1200: {
                        slidesPerView: 4,
                        spaceBetween: calculateSpaceBetween()
                    }
                },
                on: {
                    init: function () {
                        this.params.spaceBetween = calculateSpaceBetween();
                        this.update(); // update Swiper
                    },
                    resize: function () {
                        this.update(); // update Swiper
                    }
                }
            });

            function calculateSpaceBetween() {
                let spaceBetween = 0;
                if(imagesCount === 3) {
                    spaceBetween = 20;
                } else if(imagesCount === 2) {
                    spaceBetween = 20;
                }

                return spaceBetween;
            }

        }

    }

    // swiper-single-wallpaper
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



    // Change colors by click
    $('.art-colors-list .color-btn').on('click', function() {
        var targetColorId = $(this).data('color-id');
        let sliderSingle = $('.art-gallery-all-slides-container .art-swiper-single-wallpaper');
        let sliderThumbs = $('.art-gallery-all-slides-container .art-swiper-single-wallpaper-thumbs');

        let $hiddenSlidesSingleWallpaper = sliderSingle.find('.swiper-slide[data-color-id="' + targetColorId + '"]');
        let $hiddenSlidesWallpaperThumbs = sliderThumbs.find('.swiper-slide[data-color-id="' + targetColorId + '"]');

        if ($hiddenSlidesSingleWallpaper.length === 0) {
            $hiddenSlidesSingleWallpaper = sliderSingle.find('.swiper-slide[data-color-id="0"]');
        }
        if ($hiddenSlidesWallpaperThumbs.length === 0) {
            $hiddenSlidesWallpaperThumbs = sliderThumbs.find('.swiper-slide[data-color-id="0"]');
        }


        SwiperSingleWallpaper.removeAllSlides();
        SwiperSingleWallpaperThumbs.removeAllSlides();

        $hiddenSlidesSingleWallpaper.each(function() {
            SwiperSingleWallpaper.appendSlide($(this).clone());
        });

        $hiddenSlidesWallpaperThumbs.each(function() {
            SwiperSingleWallpaperThumbs.appendSlide($(this).clone());
        });

        SwiperSingleWallpaper.update();
        SwiperSingleWallpaperThumbs.update();
    });
    $(document).ready(function() {
        $('.art-colors-list .color-btn:first').trigger('click');
    });




    // Similar Products Gallery
    let SimilarProductsGallery = new Swiper(".art-products-owl-items", {
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
