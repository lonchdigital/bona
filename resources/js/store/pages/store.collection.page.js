import $ from 'jquery';

export default async function () {
    const [
        Swiper,
        FilterSubmit,
    ] = await Promise.all([
        import('./store.collection.page/swiper'),
        import('./store.collection.page/filter-submit'),
    ]);

    Swiper.init();
    FilterSubmit.init();
}
