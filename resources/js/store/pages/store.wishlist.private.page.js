export default async function () {
    const [
        Tooltip,
    ] = await Promise.all([
        import('./store.wishlist.private.page/tooltip'),
    ]);

    Tooltip.init();
}
