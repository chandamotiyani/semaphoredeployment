/**
 * ie-11 and older browser compatibility.
 * These can be removed when we're no longer supporting older browsers.
 */
require('custom-event-polyfill');
require('date-input-polyfill');
require('abortcontroller-polyfill/dist/polyfill-patch-fetch')
require('element-closest-polyfill');
require('classlist-polyfill');
require('url-search-params-polyfill');
require('formdata-polyfill');
require ('array-from-polyfill');
require ('array-findindex-polyfill');
require('element-remove');
//require('ie-array-find-polyfill');

import Vue from 'vue';
//import App from './app.vue';
import WishlistSummary from './vue-components/wishlist.vue';
import CartSummary from './vue-components/cart-summary.vue';
import addToCart from './vue-components/add-to-cart.vue';
import addToWishlist from './vue-components/add-to-wishlist.vue';
import giftOptions from './vue-components/gift-options.vue';
import addGiftOptionButton from './vue-components/add-gift-option-button.vue';
import search from './vue-components/search.vue';

let vueMain = new Vue({ el: '#vue-container',
  components: {
    WishlistSummary,
    CartSummary,
    giftOptions,
    addToCart,
    addToWishlist,
    addGiftOptionButton,
    search
  },
});

window.refs = vueMain.$refs;
require('./layout/header');
require('./layout/popout-menu');
require('./layout/hero');
require('./components/collage');
require('./components/accordion');
require('./components/scroll-indicator');
require('./components/scroll-to-content');
require('./components/vimeo');
require('./components/dropdown');
require('./components/product-list-modal');
require('./components/back-button');
require('./components/website-search-input');
require('./layout/watch-viewport');
require('./product-main');
require('./utilities/text-break');
require('./utilities/sharing');
require('./utilities/modal');
require('./layout/exceptions-list');
require('./layout/forms');
require('./utilities/collapsible-panels');
require('./utilities/toggle-target');
require('./utilities/inputMask');
require('./shop/tabbed-panels');
require('./shop/vintage-slider');
require('./shop/ajax-actions/index');
require('./components/carousel');
//require('./components/card');
require('./utilities/pagination');
require('./events/eventCalendarView');
require('./events/eventDatePicker');
require('./events/eventViewSwitch');
require('./shop/ajax-filters');
require('./shop/ajax-actions/load-filtered-results');
require('./events/load-filtered-events');
require('./shop/single-page-checkout/index');
require('./members/forms'); // Used for member related forms: Signin, Register, Forgot Password etc.
require('./members/newsletter-subscribe');
require('./contact-form');
require('./shop/pre-fill-address');
require('./components/accept-cookies');
