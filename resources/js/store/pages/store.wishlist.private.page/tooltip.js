import $ from 'jquery';
import 'bootstrap';

export function init () {
    if (window.innerWidth > 991) {
        const tooltipWishListShare = ['<div class="tooltip tooltip-help-color" role="tooltip">',
            '<div class="arrow"></div>',
            '<div class="tooltip-inner ">',
            '</div>',
            '</div>'].join('');

        $('.btn-wish-list-share').tooltip({
            trigger: "hover", //hover focus click manual
            html: true,
            placement: "top",
            template: tooltipWishListShare,
            fallbackPlacement: [], // строго в заданому напрямку, не дає можливості при скролі позиціонувати в інші сторони
        });

    }
}
