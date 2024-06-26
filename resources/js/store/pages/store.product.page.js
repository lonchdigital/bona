import $ from 'jquery';

export default async function () {
    const [
        Tooltip,
        Swiper,
        FancyBox,
        Email,
        // SimilarProducts,
    ] = await Promise.all([
        import('./store.product.page/tooltip'),
        import('./store.product.page/swiper'),
        import('./store.product.page/fancybox'),
        import('./store.product.page/email')
        // import('./store.product.page/similar-products')
    ]);

    Tooltip.init();
    Swiper.init();
    FancyBox.init();
    Email.init();
    // SimilarProducts.init();
}
