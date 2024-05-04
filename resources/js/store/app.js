import 'bootstrap';
import $ from 'jquery';
import.meta.glob(['../../img/**']);
// import { Fancybox } from "@fancyapps/ui/dist/fancybox/fancybox.esm.js";

import ShowMenu from "./common/show-menu";
import ScrollTop from "./common/scroll-top";
import InputCounter from './common/input-counter';
import Cart from './common/cart';
import CommonEmails from './common/common-emails';
import PopUps from './common/pop-ups';
import AjaxSearchProducts from './common/ajax-search-products';
import ShowRoomVisitModal from "./common/show-room-visit-modal";


// const pages = import.meta.glob(['../../js/store/pages/*.js'], { eager: true, import: 'default' });

// console.log(pages);

/*console.log('1111');
console.log(window.location);*/

async function loadJsByPage()
{
    if (page === undefined) {
        throw new Error('[pages-loader]: page value is undefined.');
    }

    // let pageToLoad = page;

    /*const modules = {
        './pages/store.home.js' : () => import('../../js/store/pages/store.home.js'),
    };*/

    let pages;


    switch (page) {
        case 'store.catalog.filter.page':
            pages = import.meta.glob(['../../js/store/pages/store.catalog.page.js'], { eager: true, import: 'default' });
            pages['./pages/store.catalog.page.js']();
            break;
        case 'store.all-products.filter.page':
            pages = import.meta.glob(['../../js/store/pages/store.all-products.filter.page.js'], { eager: true, import: 'default' });
            pages['./pages/store.all-products.page.js']();
            break;
        case 'store.catalog.filter-group.page':
            pages = import.meta.glob(['../../js/store/pages/store.catalog.filter-group.page.js'], { eager: true, import: 'default' });
            pages['./pages/store.catalog.page.js']();
            break;
        case 'store.catalog-category.filter.page':
            pages = import.meta.glob(['../../js/store/pages/store.catalog-category.filter.page.js'], { eager: true, import: 'default' });
            pages['./pages/store.catalog.page.js']();
            break;
        case 'store.catalog.page':
            pages = import.meta.glob(['../../js/store/pages/store.catalog.page.js'], { eager: true, import: 'default' });
            pages['./pages/store.catalog.page.js']();
            break;
        case 'auth.sign-in.page':
            pages = import.meta.glob(['../../js/store/pages/auth.sign-in.page.js'], { eager: true, import: 'default' });
            pages['./pages/auth.sign-in.page.js']();
            break;
        case 'auth.sign-up.page':
            pages = import.meta.glob(['../../js/store/pages/auth.sign-up.page.js'], { eager: true, import: 'default' });
            pages['./pages/auth.sign-up.page.js']();
            break;
        case 'blog.article.page':
            pages = import.meta.glob(['../../js/store/pages/blog.article.page.js'], { eager: true, import: 'default' });
            pages['./pages/blog.article.page.js']();
            break;
        case 'blog.articles-by-category.page':
            pages = import.meta.glob(['../../js/store/pages/blog.articles-by-category.page.js'], { eager: true, import: 'default' });
            pages['./pages/blog.articles-by-category.page.js']();
            break;
        case 'blog.main.page':
            pages = import.meta.glob(['../../js/store/pages/blog.main.page.js'], { eager: true, import: 'default' });
            pages['./pages/blog.main.page.js']();
            break;
        case 'store.all-products.page':
            pages = import.meta.glob(['../../js/store/pages/store.all-products.page.js'], { eager: true, import: 'default' });
            pages['./pages/store.all-products.page.js']();
            break;
        case 'store.brand.page':
            pages = import.meta.glob(['../../js/store/pages/store.brand.page.js'], { eager: true, import: 'default' });
            pages['./pages/store.brand.page.js']();
            break;
        case 'store.brands.list.page':
            pages = import.meta.glob(['../../js/store/pages/store.brands.list.page.js'], { eager: true, import: 'default' });
            pages['./pages/store.brands.list.page.js']();
            break;
        case 'store.calculator.page':
            pages = import.meta.glob(['../../js/store/pages/store.calculator.page.js'], { eager: true, import: 'default' });
            pages['./pages/store.calculator.page.js']();
            break;
        case 'store.cart.page':
            pages = import.meta.glob(['../../js/store/pages/store.cart.page.js'], { eager: true, import: 'default' });
            pages['./pages/store.cart.page.js']();
            break;
        case 'store.catalog-category.page':
            pages = import.meta.glob(['../../js/store/pages/store.catalog-category.page.js'], { eager: true, import: 'default' });
            pages['./pages/store.catalog-category.page.js']();
            break;
        case 'store.checkout.page':
            pages = import.meta.glob(['../../js/store/pages/store.checkout.page.js'], { eager: true, import: 'default' });
            pages['./pages/store.checkout.page.js']();
            break;
        case 'store.collection.page':
            pages = import.meta.glob(['../../js/store/pages/store.collection.page.js'], { eager: true, import: 'default' });
            pages['./pages/store.collection.page.js']();
            break;
        case 'store.home':
            pages = import.meta.glob(['../../js/store/pages/store.home.js'], { eager: true, import: 'default' });
            pages['./pages/store.home.js']();
            break;
        case 'store.product.page':
            pages = import.meta.glob(['../../js/store/pages/store.product.page.js'], { eager: true, import: 'default' });
            pages['./pages/store.product.page.js']();
            break;
        case 'store.wishlist.private.page':
            pages = import.meta.glob(['../../js/store/pages/store.wishlist.private.page.js'], { eager: true, import: 'default' });
            pages['./pages/store.wishlist.private.page.js']();
            break;
    }





    // console.log(page);
    /*if (page === 'store.catalog.filter.page') {
        import.meta.glob(['../../js/store/pages/store.catalog.page.js'], { eager: true, import: 'default' });
        // pageToLoad = 'store.catalog.page';
    }

    if (page === 'store.all-products.filter.page') {
        import.meta.glob(['../../js/store/pages/store.all-products.page.js'], { eager: true, import: 'default' });
        // pageToLoad = 'store.all-products.page';
    }

    if (page === 'store.catalog.filter-group.page') {
        import.meta.glob(['../../js/store/pages/store.catalog.page.js'], { eager: true, import: 'default' });
        // pageToLoad = 'store.catalog.page';
    }

    if (page === 'store.catalog-category.filter.page') {
        import.meta.glob(['../../js/store/pages/store.catalog.page.js'], { eager: true, import: 'default' });
        // pageToLoad = 'store.catalog.page';
    }*/

    /*if (pages['./pages/' + pageToLoad + '.js']) {

        console.log('===');
        console.log('./pages/' + pageToLoad + '.js');
        console.log('===');

        pages['./pages/' + pageToLoad + '.js']();
    }*/

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


