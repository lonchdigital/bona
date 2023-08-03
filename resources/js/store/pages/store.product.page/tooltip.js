import $ from 'jquery';
import 'bootstrap';

export function init () {
    const tooltipColorTemplate = ['<div class="tooltip tooltip-help-color" role="tooltip">',
        '<div class="arrow"></div>',
        '<div class="tooltip-inner ">',
        '</div>',
        '</div>'].join('');
    $('.link-color').tooltip({
        trigger: "hover", //hover focus click manual
        html: true,
        placement: "top",
        template: tooltipColorTemplate,
        fallbackPlacement: [], // строго в заданому напрямку, не дає можливості при скролі позиціонувати в інші сторони
    });

    $('.product-option-image').tooltip({
        trigger: "hover", //hover focus click manual
        html: true,
        placement: "top",
        template: tooltipColorTemplate,
        fallbackPlacement: [], // строго в заданому напрямку, не дає можливості при скролі позиціонувати в інші сторони
    });
}
