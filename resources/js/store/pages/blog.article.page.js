export default function initBlogArticlePage() {
    document.querySelectorAll('.js-article-share-copy').forEach((button) => {
        button.addEventListener('click', async () => {
            const originalLabel = button.textContent;

            try {
                await navigator.clipboard.writeText(button.dataset.url || window.location.href);
                button.textContent = button.dataset.copiedText || originalLabel;
                window.setTimeout(() => { button.textContent = originalLabel; }, 1800);
            } catch (error) {
                window.prompt('', button.dataset.url || window.location.href);
            }
        });
    });
}
