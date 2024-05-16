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



    // main product tabs
    let $productsTabsNav = $('.art-product-tabs .nav-tabs.product-tabs-nav li');
    $productsTabsNav.on('click', function (e) {
        $productsTabsNav.removeClass('active');
        $(this).addClass('active');
    });

    // product video tabs
    let $productVideoTabs = $('.art-product-tabs .nav-tabs.art-product-video-tabs li');
    $productVideoTabs.on('click', function (e) {
        $productVideoTabs.removeClass('active');
        $(this).addClass('active');
    });

}
