//import $ from "jquery";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css";

document.addEventListener("DOMContentLoaded", function () {
    flatpickr(".datepicker", {
        enableTime: true,
        dateFormat: "Y-m-d H:i:S"
    });
});
