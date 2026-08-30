function initHero(hero) {
    const slides = [...hero.querySelectorAll('[data-hero-slide]')];
    const dots = [...hero.querySelectorAll('[data-hero-dot]')];
    const currentLabel = hero.querySelector('[data-hero-current]');
    let current = 0;

    if (slides.length < 2) {
        return;
    }

    const show = (index) => {
        current = (index + slides.length) % slides.length;

        slides.forEach((slide, slideIndex) => {
            const active = current === slideIndex;
            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', String(!active));
        });

        dots.forEach((dot, dotIndex) => {
            const active = current === dotIndex;
            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-selected', String(active));
        });

        if (currentLabel) {
            currentLabel.textContent = String(current + 1).padStart(2, '0');
        }
    };

    dots.forEach((dot, index) => dot.addEventListener('click', () => show(index)));
    hero.querySelector('[data-hero-previous]')?.addEventListener('click', () => show(current - 1));
    hero.querySelector('[data-hero-next]')?.addEventListener('click', () => show(current + 1));

    hero.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') show(current - 1);
        if (event.key === 'ArrowRight') show(current + 1);
    });
}

export default {
    init() {
        document.querySelectorAll('[data-home-hero]').forEach(initHero);
    },
};
