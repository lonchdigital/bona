
export default async function () {

    const [
        Swiper,
        InstagramLightbox,
    ] = await Promise.all([
        import('./store.home/swiper'),
        import('./store.home/instagram-lightbox'),
    ]);

    Swiper.init();
    InstagramLightbox.init();
}
