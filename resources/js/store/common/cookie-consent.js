const STORAGE_KEY = 'bona_cookie_consent_v1';
const ACCEPTED = 'all';
const NECESSARY = 'necessary';

// Google Consent Mode v2 ("advanced"): the Google tag always loads so GA4 can
// receive cookieless, identifier-free pings and model visitors who decline.
// Analytics and advertising cookies are only allowed after "Accept all".
function consentState(value) {
    const granted = value === ACCEPTED ? 'granted' : 'denied';

    return {
        ad_storage: granted,
        ad_user_data: granted,
        ad_personalization: granted,
        analytics_storage: granted,
        functionality_storage: 'granted',
        security_storage: 'granted',
    };
}

function ensureGtag() {
    window.dataLayer = window.dataLayer || [];
    window.gtag = window.gtag || function gtag() {
        window.dataLayer.push(arguments);
    };
}

function setDefaultGoogleConsent() {
    ensureGtag();
    window.gtag('consent', 'default', { ...consentState(NECESSARY), wait_for_update: 500 });
    window.gtag('set', 'ads_data_redaction', true);
}

function updateGoogleConsent(value) {
    ensureGtag();
    window.gtag('consent', 'update', consentState(value));
}

function loadGoogleAnalytics(measurementId) {
    if (!measurementId || document.querySelector('script[data-bona-google-analytics]')) {
        return;
    }

    const script = document.createElement('script');
    script.async = true;
    script.dataset.bonaGoogleAnalytics = 'true';
    script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(measurementId)}`;
    document.head.appendChild(script);

    window.gtag('js', new Date());
    window.gtag('config', measurementId, safePageLocation());
}

// Signed links (payment pages, e-mail links) must not leave their signature
// in analytics reports.
const SENSITIVE_QUERY_PARAMETERS = ['signature', 'expires', 'token', 'hash'];

function safePageLocation() {
    try {
        const url = new URL(window.location.href);
        const sensitive = SENSITIVE_QUERY_PARAMETERS.filter((name) => url.searchParams.has(name));
        if (!sensitive.length) return {};

        sensitive.forEach((name) => url.searchParams.delete(name));

        return { page_location: url.toString() };
    } catch (error) {
        return {};
    }
}

function removeGoogleAnalyticsCookies() {
    const host = window.location.hostname;
    const domains = ['', host, `.${host}`, `.${host.split('.').slice(-3).join('.')}`, `.${host.split('.').slice(-2).join('.')}`];

    document.cookie.split(';').map((cookie) => cookie.split('=')[0].trim())
        .filter((name) => /^_ga(_|$)|^_gid$|^_gat/.test(name))
        .forEach((name) => {
            domains.forEach((domain) => {
                document.cookie = `${name}=; Max-Age=0; path=/${domain ? `; domain=${domain}` : ''}`;
            });
        });
}

function readChoice() {
    try {
        const value = window.localStorage.getItem(STORAGE_KEY);

        return value === ACCEPTED || value === NECESSARY ? value : null;
    } catch (error) {
        return null;
    }
}

function saveChoice(value) {
    try {
        window.localStorage.setItem(STORAGE_KEY, value);
    } catch (error) {
        // A privacy-focused browser may block storage. The selected consent
        // still applies for the current page without breaking the storefront.
    }
}

function showBanner(banner) {
    banner.hidden = false;
    window.requestAnimationFrame(() => banner.classList.add('is-visible'));
}

function hideBanner(banner) {
    banner.classList.remove('is-visible');
    window.setTimeout(() => {
        banner.hidden = true;
    }, 220);
}

function applyChoice(choice) {
    updateGoogleConsent(choice);

    if (choice !== ACCEPTED) {
        removeGoogleAnalyticsCookies();
    }

    window.dispatchEvent(new CustomEvent('bona:cookie-consent', {
        detail: { choice },
    }));
}

function init() {
    const banner = document.querySelector('[data-cookie-consent]');

    if (!banner) {
        return;
    }

    const measurementId = banner.dataset.googleAnalyticsId || '';
    const existingChoice = readChoice();

    // Denied by default before the tag loads; an earlier "Accept all" is
    // restored straight away so the first page view carries cookies.
    setDefaultGoogleConsent();
    if (existingChoice === ACCEPTED) {
        updateGoogleConsent(ACCEPTED);
    }
    loadGoogleAnalytics(measurementId);

    if (!existingChoice) {
        showBanner(banner);
    }

    banner.querySelector('[data-cookie-consent-accept]')?.addEventListener('click', () => {
        saveChoice(ACCEPTED);
        applyChoice(ACCEPTED);
        hideBanner(banner);
    });

    banner.querySelector('[data-cookie-consent-necessary]')?.addEventListener('click', () => {
        saveChoice(NECESSARY);
        applyChoice(NECESSARY);
        hideBanner(banner);
    });

    document.querySelectorAll('[data-cookie-settings]').forEach((button) => {
        button.addEventListener('click', () => {
            showBanner(banner);
            banner.querySelector('button')?.focus();
        });
    });
}

export function hasAnalyticsConsent() {
    return readChoice() === ACCEPTED;
}

// Sent regardless of the cookie choice: under denied consent gtag strips
// identifiers and transmits a cookieless ping instead.
export function trackGoogleEvent(eventName, parameters = {}) {
    if (typeof window.gtag !== 'function' || !eventName) {
        return;
    }

    window.gtag('event', eventName, parameters);

    // Lead forms keep their historical event names and also report the GA4
    // recommended generate_lead, so one key event covers every form.
    if (String(eventName).startsWith('submit_form_')) {
        window.gtag('event', 'generate_lead', { lead_source: eventName });
    }
}

export default { init };
