import Vue from 'vue';
import { Notification } from './utilities/notification';
import { CartUpdatedNotification } from './utilities/notifications/cart-updated';
import debounce from 'lodash/debounce';
import 'url-search-params-polyfill';
import 'whatwg-fetch'; 
import moment from 'moment';

export const state = Vue.observable({ // this is the magic
  lineItems: [],
  adjustments: [],
  total: 0,
  wishlistItems: [],
  removeWishlistItems: [],
  giftOptions: [],
  giftOptions: [],
  isLoading: false,
});

export const mutations = {
  setLineItems: (val) => state.lineItems = val,
  setAdjustments: (val) => state.adjustments = val,
  setTotal: (val) => state.total = val,
  setWishlistItems: (val) => state.wishlistItems = val,
  setGiftOptions: (val) => state.giftOptions = val,
  setIsLoading: (val) => state.isLoading = val,
  setRemoveWishlistItems: (val) => state.removeWishlistItems = val,

  //setGiftOptions: (val) => state.giftOptions = val
};

export const getters = {
  lineItems: () => state.lineItems,
  adjustments: () => state.adjustments,
  total: () => state.total,
  wishlistItems: () => state.wishlistItems,
  giftOptions: () => state.giftOptions,
  isLoading: () => state.isLoading,
  removeWishlistItems: () => state.removeWishlistItems,

  //giftOptions: () => state.wishlistItems,
}

export const actions = {
  async updateCart(data, notify) {
    mutations.setIsLoading(true);
    //console.log('request', data);

    let response = await fetch(`${window.location.origin}/commerce/cart/update-cart`, {
      method: 'POST',
      body: new URLSearchParams( data ),
      headers: {
        "Content-Type": 'application/x-www-form-urlencoded; charset=UTF-8',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-Token': window.csrfTokenValue,
      },
    });

    let json = await response.json();

    let listModal = document.querySelector('.product-list-modal--active');
    if(typeof listModal !=="undefined" && listModal !==null) {
      listModal.classList.remove('product-list-modal--active');
    }
    document.querySelector('body').classList.remove('fixed');

    if(json.success == true) {

      let msg = 'Cart updated';

      let lineItems = Object.keys(json.cart.lineItems).length ? json.cart.lineItems : [];
      mutations.setLineItems(lineItems);
      mutations.setAdjustments(json.cart.orderAdjustments);
      mutations.setTotal(json.cart.itemTotal);

      // Update the cart items total in the header.
      let cartCountEl = document.querySelectorAll('.js-cart-items');
      if(typeof cartCountEl !== "undefined" && typeof cartCountEl != null) {
        cartCountEl.forEach(function(item) {
          item.innerHTML = json.cart.totalQty;
        });
      }

      // slide out the panel
      if(notify == 'panel') {
        new CartUpdatedNotification({ type: 'add-to-cart', message: msg });
      } else {
        new Notification({ type: 'add-to-cart', message: msg });
      }

    } else {
      let error = '';
      let slideOut = 0;
      for (var prop in json.errors) {
        error = json.errors[prop];
        console.log(error);
        if(prop.includes('noInsider')) {
          window.location.hash = 'sign-in-no-insider';
          window.product = data;
          slideOut = 1;
        } else if(prop.includes('memberOnly')) {
          // Show sign-in panel if this is a members only product
          window.location.hash = 'sign-in';
          window.product = data;
          slideOut = 1;
        }
        break;
      }
      //Chanda - Em said to turn of the top notification hence commenting following line:
      if(slideOut == 0 || slideOut === 0 || slideOut == false) {
        new Notification({ type: 'add-to-cart', message: error});
      }
    }

    mutations.setIsLoading(false);

    // create and dispatch the event
    var event = new CustomEvent("updated-cart", {});
    document.dispatchEvent(event);

  },

  async getCart() {
    let self = this;
    mutations.setIsLoading(true);
    let response = await fetch(`${window.location.origin}/commerce/cart/get-cart`, {
      method: 'POST',
      body: new URLSearchParams({
        'action' : 'commerce/cart/get-cart',
        'CRAFT_CSRF_TOKEN' : window.csrfTokenValue,
      }),
      headers: {
        "Content-Type": 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        //'X-CSRF-Token': window.csrfTokenValue
      },
    });

    let json = await response.json();

    if(json && Object.keys(json.cart)) {

      if(Object.keys(json.cart.lineItems).length) {

        json.cart.lineItems.forEach(async function(lineItem,index) {
          console.log('Line Item: ',lineItem);
          if(lineItem.snapshot.startDateTime){
            //Event Item
            let eventDate = moment(lineItem.snapshot.startDateTime);
            if(eventDate.isBefore(moment.now())){
              //Expired
              console.log('Expired')
              let lineItemId = lineItem.id;
              const data = {
                qty: 0,
                lineItemId: lineItemId,
                action: '/commerce/cart/update-cart',
              };
              data['lineItems['+lineItemId+'][remove]'] = true; // posting a value to this input name removes item.
              const updateCart = await self.updateCart(data);
              console.log('Update Cart Response: ',updateCart);
            }
          }
        });
        
        mutations.setLineItems(json.cart.lineItems);
        mutations.setAdjustments(json.cart.orderAdjustments);
        mutations.setTotal(json.cart.itemTotal);

        console.log(json.cart);
      }

    }


    mutations.setIsLoading(false);
  },

  async toggleWishlist(url) {
    let response = await fetch(url, {
      method: 'GET',
      headers: {
        "Content-Type": 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-Token': window.csrfTokenValue,
      },
    });

    let json = await response.json();

    if(json.success) {

    }

    await this.updateWishlistItems();
  },

  removeWishlistItems: debounce(async function(removeIds, listId) {
    mutations.setRemoveWishlistItems(removeIds);
   // mutations.setIsLoading(true);

    let data = {
      listId: listId,
      elementId: removeIds,
    }

    let response = await fetch('/products/remove-from-wishlist', {
      method: 'POST',
      body: new URLSearchParams( data ),
      headers: {
        "Content-Type": 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-Token': window.csrfTokenValue,
      },
    });

    let json = await response;


    mutations.setRemoveWishlistItems([]);
    await this.updateWishlistItems();

    mutations.setIsLoading(false);
  }, 1000),

  async removeFromWishlist(removeUrl) {

    mutations.setIsLoading(true);
    let response = await fetch(removeUrl, {
      method: 'POST',
      headers: {
        "Content-Type": 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-Token': window.csrfTokenValue,
      },
    });

    let json = await response.json();

    this.updateWishlistItems();

    mutations.setIsLoading(false);
  },

  async updateWishlistItems() {

    mutations.setGiftOptions([]);

    let response = await fetch(`${window.location.origin}/products/wishlist`, {
      method: 'GET',
      headers: {
        "Content-Type": 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        //'X-CSRF-Token': window.csrfTokenValue,
      },
    });

    let json = await response.json();
    //if(json.success) {
      mutations.setWishlistItems(json);
  //  }
  },

  async getGiftOptions(productId, lineitemId, purchasableId) {

    if(typeof productId == "undefined") {
      return [];
    }

    mutations.setIsLoading(true);

    let response = await fetch(`/products/gift-options/${productId}/${lineitemId ? lineitemId : ''}?purchasableId=${purchasableId}`, {
      method: 'GET',
      headers: {
        "Content-Type": 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-Token': window.csrfTokenValue,
      },
    });

    let json = await response.json();
    //if(json.success) {
      mutations.setGiftOptions(json);
      mutations.setIsLoading(false);
  //  }
  }
}

Object.defineProperty(Vue.prototype, '$giftOptions', {
  get() { return state.giftOptions; },
  set(value) { state.giftOptions = value; }
});

Object.defineProperty(Vue.prototype, '$isLoading', {
  get() { return state.isLoading; },
  set(value) { state.isLoading = value; }
});