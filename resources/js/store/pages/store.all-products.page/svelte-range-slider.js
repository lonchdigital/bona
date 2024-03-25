import $ from 'jquery';
import RangeSlider from "/node_modules/svelte-range-slider-pips/dist/svelte-range-slider-pips.mjs";

export function init () {
    const CurrencyFirst = $("#currency-first-main");
    const CurrencyLast = $("#currency-last-main");
    const CurrencyFirstFull = $("#currency-first-full");
    const CurrencyLastFull = $("#currency-last-full");
    const CurrencyFirstFullMobile = $("#currency-first-full-m");
    const CurrencyLastFullMobile = $("#currency-last-full-m");

    let PriceSlider = new RangeSlider({
        target: $('#price-slider')[0],
        props: {
            min: parseFloat(CurrencyFirst.attr('min')),
            max: parseFloat(CurrencyLast.attr('max')),
            values: [CurrencyFirst.val() ? CurrencyFirst.val() : CurrencyFirst.attr('min'), CurrencyLast.val() ? CurrencyLast.val() : CurrencyLast.attr('max')],
            step: 1,
            range: true,
            float: true,
            suffix: ' ' + store.base_currency_name_short,

        }
    });

    let PriceSliderFull = new RangeSlider({
        target: $('#price-slider-full')[0],
        props: {
            min: parseFloat(CurrencyFirstFull.attr('min')),
            max: parseFloat(CurrencyLastFull.attr('max')),
            values: [CurrencyFirstFull.val() ? CurrencyFirstFull.val() : CurrencyFirstFull.attr('min'), CurrencyLastFull.val() ? CurrencyLastFull.val() : CurrencyLastFull.attr('max')],
            step: 1,
            range: true,
            float: true,
            suffix: ' ' + store.base_currency_name_short,
        }
    });

    let PriceSliderFullMobile = new RangeSlider({
        target: $('#price-slider-full-m')[0],
        props: {
            min: parseFloat(CurrencyFirstFullMobile.attr('min')),
            max: parseFloat(CurrencyLastFullMobile.attr('max')),
            values: [CurrencyFirstFullMobile.val() ? CurrencyFirstFullMobile.val() : CurrencyFirstFullMobile.attr('min'), CurrencyLastFullMobile.val() ? CurrencyLastFullMobile.val() : CurrencyLastFullMobile.attr('max')],
            step: 1,
            range: true,
            float: true,
            suffix: ' ' + store.base_currency_name_short,
        }
    });

    //? #price-slider
    //змінює значення при використанні повзунка
    PriceSlider.$on('change', function (e) {
        CurrencyFirst.val(e.detail.values[0]).trigger('change');
        CurrencyLast.val(e.detail.values[1]).trigger('change');
    });

    //змінює значення в інпутах
    CurrencyFirst.change(function () {
        PriceSlider.$set({ values: [CurrencyFirst.val() ? CurrencyFirst.val() : CurrencyFirst.attr('min'), CurrencyLast.val() ? CurrencyLast.val() : CurrencyLast.attr('max')] });
    });

    CurrencyLast.change(function (event) {
        PriceSlider.$set({ values: [CurrencyFirst.val() ? CurrencyFirst.val() : CurrencyFirst.attr('min'), CurrencyLast.val() ? CurrencyLast.val() : CurrencyLast.attr('max')] });
    });

    //? #price-slider-full
    //змінює значення при використанні повзунка
    PriceSliderFull.$on('change', function (e) {
        CurrencyFirstFull.val(e.detail.values[0]).trigger('change');
        CurrencyLastFull.val(e.detail.values[1]).trigger('change');
    });

    //змінює значення в інпутах
    CurrencyFirstFull.change(function (event) {
        PriceSliderFull.$set({ values: [CurrencyFirstFull.val() ? CurrencyFirstFull.val() : CurrencyFirstFull.attr('min'), CurrencyLastFull.val() ? CurrencyLastFull.val() : CurrencyLastFull.attr('max')] });
    });

    CurrencyLastFull.change(function (event) {
        PriceSliderFull.$set({ values: [CurrencyFirstFull.val() ? CurrencyFirstFull.val() : CurrencyFirstFull.attr('min'), CurrencyLastFull.val() ? CurrencyLastFull.val() : CurrencyLastFull.attr('max')] });
    });

    //? #price-slider-full-m
    PriceSliderFullMobile.$on('change', function (e) {
        CurrencyFirstFullMobile.val(e.detail.values[0]).trigger('change');
        CurrencyLastFullMobile.val(e.detail.values[1]).trigger('change');
    });

    //змінює значення в інпутах
    CurrencyFirstFullMobile.change(function (event) {
        PriceSliderFullMobile.$set({ values: [CurrencyFirstFullMobile.val() ? CurrencyFirstFullMobile.val() : CurrencyFirstFullMobile.attr('min'), CurrencyLastFullMobile.val() ? CurrencyLastFullMobile.val() : CurrencyLastFullMobile.attr('max')] });
    });

    CurrencyLastFullMobile.change(function (event) {
        PriceSliderFullMobile.$set({ values: [CurrencyFirstFullMobile.val() ? CurrencyFirstFullMobile.val() : CurrencyFirstFullMobile.attr('min'), CurrencyLastFullMobile.val() ? CurrencyLastFullMobile.val() : CurrencyLastFullMobile.attr('max')] });
    });
}
