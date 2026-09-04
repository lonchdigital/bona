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


// Page-only code (catalog filters, product galleries, checkout masks, video
// players) used to be bundled into every page. Keep each page behind its own
// loader so the homepage downloads only what it can actually execute.
const pages = import.meta.glob(['../../js/store/pages/*.js'], { import: 'default' });

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

    if (pageToLoad === 'store.catalog.manufacturer.page') {
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

    const pageLoader = pages['./pages/' + pageToLoad + '.js'];

    if (pageLoader) {
        const initPage = await pageLoader();
        await initPage();
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

    // Fancybox and Inputmask are sizeable legacy dependencies. Most pages,
    // including the homepage, have no controls that use them, so do not make
    // them part of the critical JavaScript path.
    const optionalInitializers = [];

    if (document.querySelector('[data-fancybox]:not(#user-choose-doors-success)')
        || document.querySelector('#user-choose-doors')) {
        optionalInitializers.push(
            import('./common/pop-ups').then(({ default: PopUps }) => PopUps.init())
        );
    }

    if (document.querySelector('.visit-time')) {
        optionalInitializers.push(
            import('./common/show-room-visit-modal').then(({ default: ShowRoomVisitModal }) => ShowRoomVisitModal.init())
        );
    }

    await Promise.all(optionalInitializers);
}

$(function () {
    Promise.all([init(), loadJsByPage()]).catch((error) => {
        console.error('[storefront-init]', error);
    });
});
