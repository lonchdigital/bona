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
import HomeStyleSelector from './common/home-style-selector';
import ProductCardColors from './common/product-card-colors';
import ProductComparison from './common/product-comparison';
import MobileBottomNavigation from './common/mobile-bottom-navigation';
import LeadModals from './common/lead-modals';

// console.log('1111');


const pages = import.meta.glob(['../../js/store/pages/*.js'], { eager: true, import: 'default' });

/*
console.log(window.location);*/

async function loadJsByPage()
{
    if (page === undefined) {
        throw new Error('[pages-loader]: page value is undefined.');
    }

    let pageToLoad = page.startsWith('localized.')
        ? page.slice('localized.'.length)
        : page;

    if (pageToLoad === 'store.catalog.filter.page') {
        pageToLoad = 'store.catalog.page';
    }

    if (pageToLoad === 'store.all-products.filter.page') {
        pageToLoad = 'store.all-products.page';
    }

    if (pageToLoad === 'store.catalog.filter-group.page') {
        pageToLoad = 'store.catalog.page';
    }

    if (pageToLoad === 'store.catalog-category.filter.page') {
        pageToLoad = 'store.catalog.page';
    }

    if (pageToLoad === 'store.products-rucky-by-availability.page' || pageToLoad === 'store.products-rucky-by-availability.filter.page') {
        pageToLoad = 'store.catalog.page';
    }

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
        WishList.init(),
        CommonEmails.init(),
        PopUps.init(),
        ShowRoomVisitModal.init(),
        CommonCode.init(),
        SiteHeader.init(),
        StorefrontSearch.init(),
        HomeHero.init(),
        HomeStyleSelector.init(),
        ProductCardColors.init(),
        ProductComparison.init(),
        MobileBottomNavigation.init(),
        LeadModals.init()
    ]);
}

$(function () {
    init();
    loadJsByPage();
});
