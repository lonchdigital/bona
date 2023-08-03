import $ from "jquery";
export default {
    init: async function () {
        //menu
        showMenu(
            $(".header-main-languages .current-lang span"),
            $(".header-main-languages .current-lang .sub-menu"),
            $(".header-main-languages .current-lang")
        );

        //basket
        showMenu(
            $(".header-main-desktop .header-main-others .basket-basket-list .basket-link"),
            $(".header-main-desktop .header-main-others .basket-basket-list .basket-sub-menu"),
            $(".header-main-desktop .header-main-others .basket-basket-list")
        );

        //basket mobile
        showMenu(
            $(".header-main-mobile .header-main-others .basket-basket-list .basket-link"),
            $(".header-main-mobile .header-main-others .basket-basket-list .basket-sub-menu"),
            $(".header-main-mobile .header-main-others .basket-basket-list")
        );

        //user profile
        showMenu(
            $(".header-main-desktop .header-main-others .user-profile-list .user-profile-link"),
            $(".header-main-desktop .header-main-others .user-profile-list .user-profile-sub-menu"),
            $(".header-main-desktop .header-main-others .user-profile-list")
        );

        showMenu($(".header-main-menu .wallpaper-menu .nolink"),
            $(".header-main-menu .wallpaper-menu .sub-menu"),
            $(".header-main-menu .wallpaper-menu")
        );

        showMenu($(".header-main-menu .brand-menu .nolink"),
            $(".header-main-menu .brand-menu .sub-menu"),
            $(".header-main-menu .brand-menu")
        );

        //TODO: check if we need this
        //showMenu($(".b-archive-catalog-filter-left"), $(".b-archive-catalog-filter-top .btn-filter, .b-archive-catalog-filter-left .btn-close"));

        if (window.innerWidth > 991) {
            showMenu(
                $(".header-main-desktop .search .i-search"),
                $(".header-main-desktop .search .sub-menu"),
                $(".header-main-desktop .search")
            );
        }
        if (window.innerWidth < 992) {
            showMenu(
                $(".menu-mobile .header-main-others .basket-list .basket"),
                $(".menu-mobile .header-main-others .basket-list .sub-menu"),
                $(".menu-mobile .header-main-others .basket-list")
            );
        }

    }
}

function showMenu(link, menu, burger) {
    $(document).mouseup(function (e) {
        if (burger.is(e.target) || burger.has(e.target).length !== 0) {
            if (!burger.hasClass('active')) {
                menu.addClass('active');
                burger.addClass('active');
                link.addClass('active');
            } else {
                if ((link.is(e.target) || link.has(e.target).length !== 0)) {
                    if (link.hasClass('active')) {
                        menu.removeClass('active'); // скрываем его
                        burger.removeClass('active');
                        link.removeClass('active');
                    }
                }
            }
        } else {
            if (!menu.is(e.target) // если клик был не по нашему блоку
                && menu.has(e.target).length === 0) { // и не по его дочерним элементам
                menu.removeClass('active'); // скрываем его
                burger.removeClass('active');
                link.removeClass('active');
            }
        }
    });
}
