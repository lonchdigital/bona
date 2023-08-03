import $ from 'jquery';
import { handlePasswordEye } from "../components/password-eye.js";

export default function () {
    $(".password-input").each(function () {
        handlePasswordEye($(this));
    });
}
