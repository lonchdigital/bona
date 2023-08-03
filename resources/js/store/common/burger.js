import $ from "jquery";

export default {
    init: async function () {
        $(document).mouseup(function (e) { // событие клика по веб-документу
            const menu = $(".menu-mobile"); // тут указываем ID элемента
            const burger = $(".menu-burger"); // тут указываем ID элемента

            if (burger.is(e.target) || burger.has(e.target).length !== 0) {
                if (!burger.hasClass('active')) {
                    menu.addClass('active');
                    burger.addClass('active');
                    $('.header').addClass('active')
                    $('body').addClass('lock');
                    $('.menu-burger').addClass('active');
                } else {
                    menu.removeClass('active'); // скрываем его
                    burger.removeClass('active');
                    $('.header').removeClass('active')
                    $('body').removeClass('lock');
                    $('.menu-burger').removeClass('active');
                }
            }
            else {
                if (!menu.is(e.target) // если клик был не по нашему блоку
                    && menu.has(e.target).length === 0
                    && window.innerWidth > 767
                    && window.innerWidth < 992
                ) { // и не по его дочерним элементам
                    menu.removeClass('active'); // скрываем его
                    burger.removeClass('active');
                    $('.header').removeClass('active')
                    $('body').removeClass('lock');
                    $('.menu-burger').removeClass('active');
                }
            }
        });
    }
}
