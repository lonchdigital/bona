import $ from 'jquery';
import 'bootstrap';

export function init () {
    $(function () {
        const tooltipFieldError = ['<div class="tooltip tooltip-help-info" role="tooltip">',
            '<div class="arrow"></div>',
            '<div class="tooltip-inner ">',
            '</div>',
            '</div>'].join('');
        $('.i-info').tooltip({
            trigger: "hover", //hover focus click manual
            html: true,
            placement: "top",
            template: tooltipFieldError,
            // fallbackPlacement: [], // строго в заданому напрямку, не дає можливості при скролі позиціонувати в інші сторони
        });
    });
}
