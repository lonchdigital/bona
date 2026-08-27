import $ from 'jquery';
import Inputmask from "inputmask";
export default {
    init: async function () {
        Inputmask({mask:"+38(099)999-99-99"}).mask($(".phone"));
        Inputmask({mask:"+38(099)999-99-99"}).mask($(".phone-field"));
        Inputmask({mask:"99:99"}).mask($(".visit-time"));
    }
}
