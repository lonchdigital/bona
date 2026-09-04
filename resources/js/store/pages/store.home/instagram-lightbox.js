function parseItems(modal) {
    const payload = modal.querySelector('[data-instagram-lightbox-data]');

    if (!payload) return [];

    try {
        const items = JSON.parse(payload.textContent || '[]');

        return Array.isArray(items) ? items : [];
    } catch (error) {
        console.error('[instagram-lightbox] Invalid feed payload', error);

        return [];
    }
}

function visibleFocusableElements(modal) {
    return Array.from(modal.querySelectorAll(
        'button:not([disabled]), a[href], video[controls]:not([hidden])'
    )).filter((element) => !element.closest('[hidden]') && element.offsetParent !== null);
}

export function init() {
    const section = document.querySelector('.bona-instagram');
    const modal = section?.querySelector('[data-instagram-lightbox]');

    if (!section || !modal || modal.dataset.instagramLightboxBound === 'true') return;

    const items = parseItems(modal);
    const dialog = modal.querySelector('.bona-instagram-lightbox__dialog');
    const mediaFrame = modal.querySelector('.bona-instagram-lightbox__media');
    const details = modal.querySelector('.bona-instagram-lightbox__details');
    const image = modal.querySelector('[data-instagram-modal-image]');
    const video = modal.querySelector('[data-instagram-modal-video]');
    const caption = modal.querySelector('[data-instagram-modal-caption]');
    const date = modal.querySelector('[data-instagram-modal-date]');
    const counter = modal.querySelector('[data-instagram-modal-counter]');
    const permalink = modal.querySelector('[data-instagram-modal-permalink]');
    const closeButton = modal.querySelector('[data-instagram-modal-close]');
    const previousButton = modal.querySelector('[data-instagram-modal-prev]');
    const nextButton = modal.querySelector('[data-instagram-modal-next]');
    const stats = modal.querySelector('.bona-instagram-lightbox__stats');
    const likesWrapper = modal.querySelector('[data-instagram-modal-likes-wrap]');
    const likes = modal.querySelector('[data-instagram-modal-likes]');
    const commentsWrapper = modal.querySelector('[data-instagram-modal-comments-wrap]');
    const comments = modal.querySelector('[data-instagram-modal-comments]');

    if (!items.length || !dialog || !mediaFrame || !details || !image || !video || !caption || !counter) return;

    modal.dataset.instagramLightboxBound = 'true';

    const documentLocale = document.documentElement.lang?.toLowerCase().startsWith('ru')
        ? 'ru-RU'
        : 'uk-UA';
    const numberFormatter = new Intl.NumberFormat(documentLocale);
    const dateFormatter = new Intl.DateTimeFormat(documentLocale, {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
    let activeIndex = 0;
    let activeMediaRatio = null;
    let mediaLayoutFrame = null;
    let returnFocus = null;

    const resetMediaLayout = () => {
        dialog.style.removeProperty('--bona-instagram-dialog-width');
        dialog.style.removeProperty('--bona-instagram-media-width');
    };

    const syncMediaLayout = () => {
        if (mediaLayoutFrame !== null) window.cancelAnimationFrame(mediaLayoutFrame);

        mediaLayoutFrame = window.requestAnimationFrame(() => {
            mediaLayoutFrame = null;
            resetMediaLayout();

            if (
                modal.hidden
                || !Number.isFinite(activeMediaRatio)
                || activeMediaRatio <= 0
                || window.matchMedia('(max-width: 760px)').matches
            ) return;

            const dialogRect = dialog.getBoundingClientRect();
            const mediaRect = mediaFrame.getBoundingClientRect();
            const detailsRect = details.getBoundingClientRect();
            const fittedMediaWidth = Math.floor(mediaRect.height * activeMediaRatio);

            if (fittedMediaWidth <= 0 || fittedMediaWidth >= mediaRect.width - 1) return;

            const dialogChromeWidth = Math.max(
                0,
                dialogRect.width - mediaRect.width - detailsRect.width
            );
            const fittedDialogWidth = fittedMediaWidth + detailsRect.width + dialogChromeWidth;

            if (fittedDialogWidth > window.innerWidth - 48) return;

            dialog.style.setProperty('--bona-instagram-media-width', `${fittedMediaWidth}px`);
            dialog.style.setProperty('--bona-instagram-dialog-width', `${Math.ceil(fittedDialogWidth)}px`);
        });
    };

    const setMediaRatio = (width, height) => {
        const naturalWidth = Number(width);
        const naturalHeight = Number(height);

        activeMediaRatio = naturalWidth > 0 && naturalHeight > 0
            ? naturalWidth / naturalHeight
            : null;
        syncMediaLayout();
    };

    const syncActiveMediaRatio = () => {
        if (!video.hidden && video.videoWidth > 0 && video.videoHeight > 0) {
            setMediaRatio(video.videoWidth, video.videoHeight);
            return;
        }

        if (!image.hidden && image.naturalWidth > 0 && image.naturalHeight > 0) {
            setMediaRatio(image.naturalWidth, image.naturalHeight);
        }
    };

    image.addEventListener('load', syncActiveMediaRatio);
    video.addEventListener('loadedmetadata', syncActiveMediaRatio);
    window.addEventListener('resize', syncMediaLayout, { passive: true });

    const stopVideo = () => {
        video.pause();
        video.removeAttribute('src');
        video.removeAttribute('poster');
        video.load();
        video.hidden = true;
    };

    const setMetric = (wrapper, valueNode, value, label) => {
        if (!wrapper || !valueNode) return false;

        const metric = Number(value);
        const available = value !== null && value !== undefined && Number.isFinite(metric);

        wrapper.hidden = !available;

        if (available) {
            const formatted = numberFormatter.format(metric);
            valueNode.textContent = formatted;
            wrapper.setAttribute('aria-label', `${label}: ${formatted}`);
        } else {
            wrapper.removeAttribute('aria-label');
        }

        return available;
    };

    const render = (requestedIndex) => {
        activeIndex = (requestedIndex + items.length) % items.length;
        activeMediaRatio = null;
        resetMediaLayout();

        const item = items[activeIndex] || {};
        const itemCaption = String(item.caption || modal.dataset.emptyCaption || 'Instagram');
        const previewUrl = String(item.image_url || '');
        const playableUrl = String(item.content_url || '');
        const contentUrl = playableUrl || previewUrl;
        const isPlayableVideo = item.media_type === 'VIDEO' && playableUrl !== '';

        stopVideo();
        image.hidden = true;
        image.removeAttribute('src');

        if (isPlayableVideo) {
            video.poster = previewUrl;
            video.src = contentUrl;
            video.setAttribute('aria-label', itemCaption);
            video.hidden = false;
            video.load();
        } else {
            image.src = contentUrl;
            image.alt = itemCaption;
            image.hidden = false;
        }

        caption.textContent = itemCaption;
        counter.textContent = `${activeIndex + 1} / ${items.length}`;

        const hasLikes = setMetric(
            likesWrapper,
            likes,
            item.like_count,
            modal.dataset.likesLabel || ''
        );
        const hasComments = setMetric(
            commentsWrapper,
            comments,
            item.comments_count,
            modal.dataset.commentsLabel || ''
        );

        if (stats) stats.hidden = !hasLikes && !hasComments;

        if (date) {
            const timestamp = item.timestamp ? new Date(item.timestamp) : null;
            const hasDate = timestamp instanceof Date && !Number.isNaN(timestamp.getTime());

            date.hidden = !hasDate;
            date.textContent = hasDate ? dateFormatter.format(timestamp) : '';

            if (hasDate) date.dateTime = timestamp.toISOString();
            else date.removeAttribute('datetime');
        }

        if (permalink) {
            const hasPermalink = typeof item.permalink === 'string' && item.permalink !== '';
            permalink.hidden = !hasPermalink;
            if (hasPermalink) permalink.href = item.permalink;
        }

        window.requestAnimationFrame(syncActiveMediaRatio);
    };

    const close = () => {
        if (modal.hidden) return;

        stopVideo();
        activeMediaRatio = null;
        resetMediaLayout();
        modal.hidden = true;
        document.body.classList.remove('bona-instagram-lightbox-open');

        if (returnFocus && typeof returnFocus.focus === 'function') returnFocus.focus();
        returnFocus = null;
    };

    const open = (index, trigger) => {
        returnFocus = trigger || document.activeElement;
        render(index);
        modal.hidden = false;
        document.body.classList.add('bona-instagram-lightbox-open');
        window.requestAnimationFrame(syncActiveMediaRatio);
        window.setTimeout(() => closeButton?.focus(), 30);
    };

    section.querySelectorAll('[data-instagram-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const index = Number.parseInt(trigger.dataset.instagramOpen || '0', 10);
            open(Number.isFinite(index) ? index : 0, trigger);
        });
    });

    closeButton?.addEventListener('click', close);
    previousButton?.addEventListener('click', () => render(activeIndex - 1));
    nextButton?.addEventListener('click', () => render(activeIndex + 1));

    modal.addEventListener('click', (event) => {
        if (event.target === modal) close();
    });

    modal.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            close();
            return;
        }

        if (event.target !== video && event.key === 'ArrowLeft') {
            event.preventDefault();
            render(activeIndex - 1);
            return;
        }

        if (event.target !== video && event.key === 'ArrowRight') {
            event.preventDefault();
            render(activeIndex + 1);
            return;
        }

        if (event.key !== 'Tab') return;

        const focusable = visibleFocusableElements(modal);
        if (!focusable.length) return;

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
}
