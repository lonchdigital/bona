import { Fancybox } from "@fancyapps/ui/dist/fancybox/fancybox.esm.js";
import iconUrl from '$img/icon.svg';
export function init () {

    Fancybox.bind('[data-fancybox="works-gallery"]', {
        Toolbar: {
            items: {
                close_custom: {
                    tpl: `<button class="f-button btn-fancybox-close" data-fancybox-close><svg><use xlink:href="${iconUrl}#i-close"></use></svg></button>`
                }
            },
            display: {
                left: [],
                right: ["close_custom"]
            }
        },
        Carousel: {
            Navigation: {
                nextTpl: `<svg><use xlink:href="${iconUrl}#i-arrow-circle"></use></svg>`,
                prevTpl: `<svg><use xlink:href="${iconUrl}#i-arrow-circle"></use></svg>`
            },
        },
        Thumbs: false,
    });


    Fancybox.bind('[data-fancybox]', {
        hideScrollbar: false
    });
}
