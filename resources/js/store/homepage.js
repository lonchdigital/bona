// import 'bootstrap';
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


const pages = import.meta.glob(['../../js/store/pages/store.home.js'], { eager: true, import: 'default' });


async function loadJsByPage()
{
    if (page === undefined) {
        throw new Error('[pages-loader]: page value is undefined.');
    }

    let pageToLoad = page;

    if (pages['./pages/' + pageToLoad + '.js']) {
        pages['./pages/' + pageToLoad + '.js']();
    }
}

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
        ShowRoomVisitModal.init()
    ]);
}

$(function () {
    init();
    loadJsByPage();
});
