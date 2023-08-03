import $ from 'jquery';

export default async function () {
    const rm = new ReadMore($('.spoiler'), 2);

    const [
        Swiper,
    ] = await Promise.all([
        import('./store.brand.page/swiper'),
    ]);

    Swiper.init();
}

function ReadMore(element, lineNum) {
    const textMinHeight = '' + (parseInt(element.children('.hidden-text').css('line-height'), 10) * lineNum) + 'px';
    const textMaxHeight = '' + element.children('.hidden-text').css('height');

    element.children('.hidden-text').css('height', '' + textMaxHeight);
    element.children('.hidden-text').css('transition', 'height .5s');
    element.children('.hidden-text').css('height', '' + textMinHeight);

    element.append('<button class="btn btn-outline-black-custom btn-read-more d-block mx-auto py-2 py-lg-1 px-5">' + translations.read_more + '</button>');

    element.children('.btn-read-more').click(function () {
        if (element.children('.hidden-text').css('height') === textMinHeight) {
            element.children('.hidden-text').css('height', '' + textMaxHeight);
            element.children('.btn-read-more').html(translations.hide).addClass('active');
        } else {
            element.children('.hidden-text').css('height', '' + textMinHeight);
            element.children('.btn-read-more').html(translations.read_more).removeClass('active');
        }
    });
}
