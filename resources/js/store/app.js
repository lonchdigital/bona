// import 'bootstrap';
import $ from 'jquery';
import.meta.glob(['../../img/**'], {
    eager: true,
    import: 'default',
    query: '?url',
});
// import { Fancybox } from "@fancyapps/ui/dist/fancybox/fancybox.esm.js";

import ShowMenu from "./common/show-menu";
import ScrollTop from "./common/scroll-top";
import InputCounter from './common/input-counter';
import Cart from './common/cart';
import WishList from './common/wish-list';
import CommonEmails from './common/common-emails';
import PopUps from './common/pop-ups';
import ShowRoomVisitModal from "./common/show-room-visit-modal";
import CommonCode from './common/common-code';
import SiteHeader from './common/site-header';
import StorefrontSearch from './common/storefront-search';
import HomeHero from './common/home-hero';

// console.log('1111');


const pages = import.meta.glob(['../../js/store/pages/*.js'], { eager: true, import: 'default' });

/*
console.log(window.location);*/

async function loadJsByPage()
{
    if (page === undefined) {
        throw new Error('[pages-loader]: page value is undefined.');
    }

    let pageToLoad = page;

    // console.log(page);
    if (page === 'store.catalog.filter.page') {
        pageToLoad = 'store.catalog.page';
    }

    if (page === 'store.all-products.filter.page') {
        pageToLoad = 'store.all-products.page';
    }

    if (page === 'store.catalog.filter-group.page') {
        pageToLoad = 'store.catalog.page';
    }

    if (page === 'store.catalog-category.filter.page') {
        pageToLoad = 'store.catalog.page';
    }

    if (page === 'store.products-rucky-by-availability.page' || page === 'store.products-rucky-by-availability.filter.page') {
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
        ScrollTop.init(),
        ShowMenu.init(),
        Cart.init(),
        WishList.init(),
        CommonEmails.init(),
        PopUps.init(),
        ShowRoomVisitModal.init(),
        CommonCode.init(),
        SiteHeader.init(),
        StorefrontSearch.init(),
        HomeHero.init()
    ]);
}

$(function () {
    init();
    loadJsByPage();
});
