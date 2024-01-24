import { Fancybox } from "@fancyapps/ui/dist/fancybox/fancybox.esm.js";
import iconUrl from '$img/icon.svg';


export default {

    init: async function () {

        Fancybox.bind('[data-fancybox]', {
            hideScrollbar: false
        });



        //  dialog-content-warning start
        function setCookie(name, value, days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }
        function getCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
        var hasCodeBeenExecuted = getCookie("codeExecuted");
        if (!hasCodeBeenExecuted) {
            // Ваш код, который должен быть выполнен
            var button = document.getElementById("dialog-content-warning");
            button.click();

            // Устанавливаем куку, чтобы помнить, что код уже был выполнен сегодня
            setCookie("codeExecuted", "true", 1);
        }
        //  dialog-content-warning end

    }
};
