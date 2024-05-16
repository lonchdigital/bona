import 'bootstrap';
import $ from 'jquery';
import.meta.glob(['../../img/**']);

import ShowMenu from "./common/show-menu";
import ScrollTop from "./common/scroll-top";
import InputCounter from './common/input-counter';
import Cart from './common/cart';
import CommonEmails from './common/common-emails';
import PopUps from './common/pop-ups';
import AjaxSearchProducts from './common/ajax-search-products';
import ShowRoomVisitModal from "./common/show-room-visit-modal";
import CommonCode from './common/common-code';

async function init()
{
    await Promise.all([
        InputCounter.init(),
        ScrollTop.init(),
        ShowMenu.init(),
        Cart.init(),
        CommonEmails.init(),
        PopUps.init(),
        AjaxSearchProducts.init(),
        ShowRoomVisitModal.init(),
        CommonCode.init()
    ]);
}

$(function () {
    init();
});
