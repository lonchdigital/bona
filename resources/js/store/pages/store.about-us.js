
export default async function () {

    const [
        Swiper,
    ] = await Promise.all([
        import('./store.about-us/swiper')
    ]);

    Swiper.init();
}
