import $ from "jquery";

import 'swiper/css';
import Swiper from 'swiper/bundle';


export function init () {

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



}
