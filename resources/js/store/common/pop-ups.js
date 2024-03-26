import { Fancybox } from "@fancyapps/ui/dist/fancybox/fancybox.esm.js";
import iconUrl from '$img/icon.svg';


export default {

    init: async function () {

        Fancybox.bind('[data-fancybox]', {
            hideScrollbar: false
        });



        //  dialog-content-warning start
        /*var hasCodeBeenExecuted = sessionStorage.getItem("codeExecuted");
        if (!hasCodeBeenExecuted) {
            var button = document.getElementById("dialog-content-warning");
            button.click();

            sessionStorage.setItem("codeExecuted", "true");
        }*/
        //  dialog-content-warning end

    }
};
