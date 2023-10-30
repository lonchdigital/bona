import 'bootstrap';
import $ from 'jquery';
import.meta.glob(['../../img/**']);

import Burger from './common/burger';
import ShowMenu from "./common/show-menu";
import ScrollTop from "./common/scroll-top";
import InputCounter from './common/input-counter';
import AnimateScroll from './common/animate-scroll';
import WishList from './common/wish-list';
import Cart from './common/cart';
import ShowRoomVisitModal from "./common/show-room-visit-modal";

const pages = import.meta.glob(['../../js/store/pages/*.js'], { eager: true, import: 'default' })

async function loadJsByPage()
{
    if (page === undefined) {
        throw new Error('[pages-loader]: page value is undefined.');
    }

    let pageToLoad = page;
    if (page === 'store.catalog.filter.page') {
        pageToLoad = 'store.catalog.page';
    }

    if (page === 'store.catalog.filter-group.page') {
        pageToLoad = 'store.catalog.page';
    }

    if (page === 'store.catalog-category.filter.page') {
        pageToLoad = 'store.catalog.page';
    }

    if (pages['./pages/' + pageToLoad + '.js']) {

        console.log('===');
        console.log('./pages/' + pageToLoad + '.js');
        console.log('===');

        pages['./pages/' + pageToLoad + '.js']();
    }
}

async function init()
{
     await Promise.all([
        InputCounter.init(),
        AnimateScroll.init(),
        Burger.init(),
        ScrollTop.init(),
        ShowMenu.init(),
        WishList.init(),
        Cart.init(),
        ShowRoomVisitModal.init()
    ]);
}

$(function () {
     init();
     loadJsByPage();
});


