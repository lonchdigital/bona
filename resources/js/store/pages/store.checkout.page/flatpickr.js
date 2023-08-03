import flatpickr from "flatpickr";
import * as Locales from "flatpickr/dist/l10n/"

export function init () {
    flatpickr.localize(Locales.default.uk); //default

    flatpickr(".datepicker", {
        minDate: new Date().fp_incr(3),
        disableMobile: "true",
        wrap: true,
        dateFormat: "d/m/Y",
        locale: Locales.default[locale],
    });
}
