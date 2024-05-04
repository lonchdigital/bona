
export default async function () {
    const [
        FancyBox,
    ] = await Promise.all([
        import('./store.works.page/fancybox')
    ]);

    FancyBox.init();
}
