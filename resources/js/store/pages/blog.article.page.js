export default function initBlogArticlePage() {
    document.querySelectorAll('.js-article-share-copy').forEach((button) => {
        button.addEventListener('click', async () => {
            const label = button.querySelector('[data-share-copy-label]');
            const originalLabel = label?.textContent || button.textContent;

            try {
                await navigator.clipboard.writeText(button.dataset.url || window.location.href);
                if (label) {
                    label.textContent = button.dataset.copiedText || originalLabel;
                } else {
                    button.textContent = button.dataset.copiedText || originalLabel;
                }

                button.classList.add('is-copied');
                window.setTimeout(() => {
                    if (label) {
                        label.textContent = originalLabel;
                    } else {
                        button.textContent = originalLabel;
                    }

                    button.classList.remove('is-copied');
                }, 1800);
            } catch (error) {
                window.prompt('', button.dataset.url || window.location.href);
            }
        });
    });

    document.querySelectorAll('.article-faq .accordion').forEach((trigger) => {
        const syncExpandedState = () => {
            trigger.setAttribute('aria-expanded', trigger.classList.contains('active') ? 'true' : 'false');
        };

        syncExpandedState();
        trigger.addEventListener('click', () => window.requestAnimationFrame(syncExpandedState));
        trigger.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            trigger.click();
        });
    });
}
