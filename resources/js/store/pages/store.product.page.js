export default async function () {
    const [
        Tooltip,
        FancyBox,
        Email,
        ProductReference,
    ] = await Promise.all([
        import('./store.product.page/tooltip'),
        import('./store.product.page/fancybox'),
        import('./store.product.page/email'),
        import('./store.product.page/product-reference'),
    ]);

    Tooltip.init();
    FancyBox.init();
    Email.init();
    ProductReference.init();
}
