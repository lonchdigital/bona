import{S as i,$ as e}from"./app-2c14b8a7.js";/* empty css               */import"./jquery-5c3435d1.js";function p(){new i(".swiper.owl-slider",{loop:!0,autoplay:{delay:3e3,disableOnInteraction:!1,pauseOnMouseEnter:!1},slidesPerView:1,pagination:{el:".swiper-pagination",clickable:!0}}),e(document).ready(function(){function t(){e(".home-page-slide-itself").each(function(){let s=e(window).width(),n=e(this).data("pc"),r=e(this).data("mob"),a=s>767?n:r;e(this).css("background-image",`url(${a})`)})}t(),e(window).resize(function(){t()})}),new i(".swiper.owl-icons",{slidesPerView:6,spaceBetween:30,navigation:{nextEl:".swiper-button-next",prevEl:".swiper-button-prev"},breakpoints:{100:{slidesPerView:2},500:{slidesPerView:3},768:{slidesPerView:4},1200:{slidesPerView:6}},on:{init:function(){this.update()},resize:function(){this.update()}}}),e(".art-instagram-owl-items.art-instagram").length>0&&new i(".art-instagram-owl-items.art-instagram",{slidesPerView:4,spaceBetween:30,pagination:{el:".swiper-pagination",clickable:!0},breakpoints:{100:{slidesPerView:3},500:{slidesPerView:4},768:{slidesPerView:5},1200:{slidesPerView:6}},on:{init:function(){this.update()},resize:function(){this.update()}}}),e(".art-quote-carousel-home.quote-carousel").length>0&&new i(".art-quote-carousel-home.quote-carousel",{slidesPerView:3,spaceBetween:30,pagination:{el:".swiper-pagination",clickable:!0},breakpoints:{100:{slidesPerView:1},500:{slidesPerView:2},1200:{slidesPerView:3}},on:{init:function(){this.update()},resize:function(){this.update()}}}),e(".swiper.art-brands-owl-items").length>0&&new i(".swiper.art-brands-owl-items",{slidesPerView:5,spaceBetween:30,pagination:{el:".swiper-pagination",clickable:!0},breakpoints:{100:{slidesPerView:2},500:{slidesPerView:5},768:{slidesPerView:4},1200:{slidesPerView:5}},on:{init:function(){this.update()},resize:function(){this.update()}}}),e(".art-products-owl-items.art-new-products").length>0&&new i(".art-products-owl-items.art-new-products",{slidesPerView:4,spaceBetween:30,pagination:{el:".swiper-pagination",clickable:!0},breakpoints:{100:{slidesPerView:1},500:{slidesPerView:2},768:{slidesPerView:3},1200:{slidesPerView:4}},on:{init:function(){this.update()},resize:function(){this.update()}}}),e(".art-products-owl-items.art-best-products").length>0&&new i(".art-products-owl-items.art-best-products",{slidesPerView:4,spaceBetween:30,pagination:{el:".swiper-pagination",clickable:!0},breakpoints:{100:{slidesPerView:1},500:{slidesPerView:2},768:{slidesPerView:3},1200:{slidesPerView:4}},on:{init:function(){this.update()},resize:function(){this.update()}}})}export{p as init};