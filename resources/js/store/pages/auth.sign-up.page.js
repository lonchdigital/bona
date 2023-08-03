import $ from 'jquery';
import Inputmask from "inputmask";
import { handlePasswordEye } from "../components/password-eye.js";

export default function () {
    $(".password-input").each(function () {
        handlePasswordEye($(this));
    });

    Inputmask({mask:"+38(099)999-99-99"}).mask($("#phone"));
}
