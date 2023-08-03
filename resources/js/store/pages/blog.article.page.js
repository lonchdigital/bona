export default async function () {
    const [
        Swiper,
        Plyr
    ] = await Promise.all([
        import('./blog.article.page/swiper'),
        import('./blog.article.page/plyr'),
    ]);

    Swiper.init();
    Plyr.init();
}
