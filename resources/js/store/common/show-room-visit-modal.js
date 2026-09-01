import $ from 'jquery';
import Inputmask from "inputmask";
export default {
    init: async function () {
        Inputmask({mask:"99:99"}).mask($(".visit-time"));
    }
}
