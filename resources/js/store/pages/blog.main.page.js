export default async function () {
    const [
        Swiper,
    ] = await Promise.all([
        import('./blog.main.page/swiper'),
    ]);

    Swiper.init();
}
