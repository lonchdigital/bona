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



        console.log('here??');
        // Hamburger Settings
        var $hamburgerHeaderIcon = $('.hamburger--collapse-r'),
            $hamburgerHeaderItems = $('#art-hamburger-menu .art-hamburger-header span'),
            $hamburgerData = $('#art-hamburger-menu .art-hamburger-data .art-list-items'),
            $hamburgerMenu = $('#art-hamburger-menu'),
            $headerSearchIcon = $('nav .navigation-bottom .navigation-bottom-right .art-search-mobile-icon'),
            $headerSearchField = $('nav .navigation-bottom .navigation-bottom-left .header-search');

        // hamburger settings
        $hamburgerHeaderIcon.on('click', function () {
            var h_this = $(this);

            console.log('click??');

            $headerSearchIcon.removeClass('is-active');
            $headerSearchField.hide();

            if(h_this.hasClass('is-active')) {
                h_this.removeClass('is-active');
                $hamburgerMenu.fadeOut();
            } else {
                h_this.addClass('is-active');
                $hamburgerMenu.fadeIn();
            }
        });

        $hamburgerHeaderItems.on('click', function(){
            var selected_item = $(this).data('id');

            $hamburgerHeaderItems.removeClass('active');
            $(this).addClass('active');

            $hamburgerData.addClass('d-none').removeClass('d-block');
            $hamburgerData.filter('[data-id="' + selected_item + '"]').removeClass('d-none').addClass('d-block');
        });


        // search settings
        $headerSearchIcon.on('click', function () {
            var h_this = $(this);

            $hamburgerHeaderIcon.removeClass('is-active');
            $hamburgerMenu.hide();

            if(h_this.hasClass('is-active')) {
                h_this.removeClass('is-active');
                $headerSearchField.fadeOut();
            } else {
                h_this.addClass('is-active');
                $headerSearchField.fadeIn();
            }
        });


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
