// The Serp Agent tracker records the page a visit started on — the article
// that brought the buyer — in sessionStorage. Order forms carry that address
// back so the server can name it when it reports a created order. Nothing
// here is personal: one URL of this site.

const STORAGE_KEY = 'sa_landing';

export function landingUrl() {
    try {
        const stored = JSON.parse(window.sessionStorage.getItem(STORAGE_KEY) || 'null');

        return stored && typeof stored.u === 'string' ? stored.u : '';
    } catch (error) {
        // Private windows and blocked storage simply leave the field empty;
        // the conversion is still reported, only without attribution.
        return '';
    }
}

function fillLandingFields() {
    const value = landingUrl();
    if (!value) return;

    document.querySelectorAll('input[name="sa_landing"]').forEach((input) => {
        input.value = value;
    });
}

export default { init: fillLandingFields };
