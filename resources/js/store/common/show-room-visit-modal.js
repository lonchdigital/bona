import $ from 'jquery';
import Inputmask from "inputmask";
export default {
    init: async function () {
        Inputmask({mask:"+38(099)999-99-99"}).mask($(".phone"));
        Inputmask({mask:"+38(099)999-99-99"}).mask($(".phone-field"));
        Inputmask({mask:"99:99"}).mask($(".visit-time"));

        if (show_visit_modal) {
            $('#modal-visit').modal('show');
        }

        if (show_designer_modal) {
            $('#modal-designer').modal('show');
        }

        if (show_taxi_modal) {
            $('#modal-taxi').modal('show');
        }

        if (show_modal_success) {
            $('#modal-success').modal('show');
        }
    }
}
