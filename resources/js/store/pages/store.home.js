import $ from 'jquery';

export default async function () {
    const [
        Swiper,
    ] = await Promise.all([
        import('./store.home/swiper'),
    ]);

    Swiper.init();
}
