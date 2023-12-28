import { Fancybox } from "@fancyapps/ui/dist/fancybox/fancybox.esm.js";
import iconUrl from '$img/icon.svg';


export default {

    init: async function () {

        Fancybox.bind('[data-fancybox]', {
            hideScrollbar: false
        });

    }

};

