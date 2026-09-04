import $ from 'jquery';

function showMenu(link, menu, wrapper) {
    if (!link.length || !menu.length || !wrapper.length) {
        return;
    }

    $(document).on('mouseup', function (event) {
        if (wrapper.is(event.target) || wrapper.has(event.target).length) {
            if (!wrapper.hasClass('active')) {
                menu.addClass('active');
                wrapper.addClass('active');
                link.addClass('active');
            } else if (link.is(event.target) || link.has(event.target).length) {
                menu.removeClass('active');
                wrapper.removeClass('active');
                link.removeClass('active');
            }
        } else if (!menu.is(event.target) && !menu.has(event.target).length) {
            menu.removeClass('active');
            wrapper.removeClass('active');
            link.removeClass('active');
        }
    });
}

export default {
    init: async function () {
        // These menus still appear on catalogue surfaces outside the global
        // header and therefore keep their existing behaviour.
        showMenu(
            $('.header-main-menu .wallpaper-menu .nolink'),
            $('.header-main-menu .wallpaper-menu .sub-menu'),
            $('.header-main-menu .wallpaper-menu')
        );
        showMenu(
            $('.header-main-menu .brand-menu .nolink'),
            $('.header-main-menu .brand-menu .sub-menu'),
            $('.header-main-menu .brand-menu')
        );
    },
};
