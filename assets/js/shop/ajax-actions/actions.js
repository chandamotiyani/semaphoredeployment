/**
 * TODO: REFACTOR IN PROGRESS. ::: Refactor is currently about 70% done. We now have AjaxForms and AjaxReloadPartial classes as well as Vue JS components - This file will be NO MORE before launch, please don't add to it.
 */


import Jump from 'jump.js';
import { easeInOutQuad } from '../../utilities/easing';
import { Notification } from '../../utilities/notification';
import preFillAddress from '../pre-fill-address'
import 'whatwg-fetch'; 

async function checkoutFormUpdateAddress(addressContainer) {

  let addressType = addressContainer.dataset.addressType;
  let submitMsg = addressContainer.querySelector('.js-msg');
  addressContainer.classList.add('loading');
  submitMsg.classList.remove('alert--success');
  submitMsg.classList.remove('alert--fail');

  let data = {
    'address[firstName]' : document.getElementsByName(`${addressType}Address[firstName]`)[0].value,
    'address[lastName]' : document.getElementsByName(`${addressType}Address[lastName]`)[0].value,
    'address[businessName]' : document.getElementsByName(`${addressType}Address[businessName]`)[0].value,
    'address[address1]' : document.getElementsByName(`${addressType}Address[address1]`)[0].value,
    'address[address2]' : document.getElementsByName(`${addressType}Address[address2]`).length ?document.getElementsByName(`${addressType}Address[address2]`)[0].value : '',
    'address[city]' : document.getElementsByName(`${addressType}Address[city]`)[0].value,
    'address[stateValue]' : document.getElementsByName(`${addressType}Address[stateValue]`)[0].value,
    'address[zipCode]' : document.getElementsByName(`${addressType}Address[zipCode]`)[0].value,
    'address[countryId]' : document.getElementsByName(`${addressType}Address[countryId]`)[0].value, 
    'address[id]' : document.getElementsByName(`${addressType}AddressId`)[0].value,
    //'address[primaryBilling]' : document.getElementsByName(`${addressType}Address[primaryBilling]`)[0].value,
    //'address[primaryShipping]' : document.getElementsByName(`${addressType}Address[primaryShipping]`)[0].value,
    'CRAFT_CSRF_TOKEN': window.csrfTokenValue,
    'action' : 'commerce/customer-addresses/save',
  };

  data = new URLSearchParams(data);

  const response = await fetch('', {
    method: 'POST',
    body: data,
    headers: {
      "Content-Type": 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    },
  });

  const json = await response.json();

  if(json.success) {

    addressContainer.classList.remove('loading');
    new Notification({ type: 'success', message: `Address Updated` });

    //TODO: Refactor this:
    const updateHTML = await fetch('', {
      method: 'GET',
    });
  
    let response = await updateHTML.text();
    let html = document.createElement('div');
    html.innerHTML = response;

    _updatePartial('.js-checkout-address', html);

  } else {
    let errorMessages = '';

    for (var error in json.errors) {
      errorMessages += json.errors[error]+'<br>';
    }

    new Notification({ type: 'success', message: `Please correct errors and try again<br>${errorMessages}`});
    addressContainer.classList.remove('loading');
  }

  Jump(submitMsg, {
    duration: 500,
    offset: -130,
    callback: undefined,
    easing: easeInOutQuad,
    a11y: false
  });
}

async function checkoutFormDeleteAddress(addressContainer) {
  let addressType = addressContainer.dataset.addressType;
  let addressId = document.getElementsByName(`${addressType}AddressId`)[0].value;
  let submitMsg = addressContainer.querySelector('.js-msg');
  addressContainer.classList.add('loading');

  let data = {
    'id' : addressId,
    'CRAFT_CSRF_TOKEN': window.csrfTokenValue,
    'action' : 'commerce/customer-addresses/delete',
  };

  data = new URLSearchParams(data);

  const response = await fetch('', {
    method: 'POST',
    body: data,
    headers: {
      "Content-Type": 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    },
  });

  const json = await response.json();

  if(json.success) {

    //TODO check if exists
    document.querySelectorAll(`[data-address-id="${addressId}"]`).forEach( (option) => {
      option.remove();
    });

    addressContainer.classList.remove('loading');

    new Notification({ type: 'error', message: `Address Deleted` });

  } else {
    let errorMessages = '';

    for (var error in json.errors) {
      errorMessages += json.errors[error]+'<br>';
    }

    addressContainer.classList.remove('loading');
    new Notification({ type: 'error', message: `Error: Could not delete address<br>${errorMessages}` });


    Jump(submitMsg, {
      duration: 500,
      offset: -130,
      callback: undefined,
      easing: easeInOutQuad,
      a11y: false
    });
  }
}

async function selectProductVariation(url, productId, disableBuyButton) {
  let productPartial = document.querySelector('.js-update-product-content');

  productPartial.classList.add('loading');
  const updateHTML = await fetch(`${url}`, {
    method: 'GET',
  });

  let response = await updateHTML.text();
  let html = document.createElement('div');
  html.innerHTML = response;

  if( _updatePartial('.js-update-product-content', html)  ) {

    //update add to cart button
   // _updatePartial('.js-product-add-to-cart', html);
    //update sticky header description
    _updatePartial('.js-sticky-cart-description', html);
    _updatePartial('.cart-sticky-header__item--price', html);
    _updatePartial('.product__status-badges', html);



    productPartial.classList.remove('loading');
    history.replaceState(document.location.host, document.title, url);

    const urlSearch = new URL(url);
    const urlParams = new URLSearchParams(urlSearch.search);
    const variantId = urlParams.get('variant');

    let variantUpdated = new CustomEvent("update-add-to-cart-props", {
      "detail": {
        productid : productId,
        purchasableid: variantId,
        disableAttr: disableBuyButton,
      }
    });
    document.dispatchEvent(variantUpdated);

  }
}


async function selectVintage(url) {
  let vintagePartial = document.querySelector('.js-vintage-select');

  vintagePartial.classList.add('loading');
  const updateHTML = await fetch(`${url}`, {
    method: 'GET',
  });

  let response = await updateHTML.text();

  let html = document.createElement('div');
  html.innerHTML = response;

  vintagePartial.classList.remove('loading');

  _updatePartial('.js-vintage-select', html);
}

async function updatePartials(productId) {


  let filterUpdatedEvent = new CustomEvent("partials-reloaded", {}); // (2)
  document.getElementById('vue-container').dispatchEvent(filterUpdatedEvent);

  const updateHTML = await fetch(`${window.location.href}`, {
    method: 'GET',
  });

  let response = await updateHTML.text();
  let html = document.createElement('div');
  html.innerHTML = response;

  
  //update the main product form (product page)
  //_updatePartial('.js-reload-product-form', html);

  //update listing
  //_updatePartial(`.js-update-product-content`, html);

  //update cart summary
  //_updatePartial('.js-checkout-summary', html);

  //update payment totals and CC
  _updatePartial('.js-payment', html);

  //update cart summary
  //_updatePartial('.cart-summary-js', html);

  //update cart item count (navigation)
  //_updatePartial('.js-cart-items', html);

    //update cart item count (navigation)
   // _updatePartial('.js-wishlist-items', html);

  // update wishlist view (incase we added the item from wishlist)
  //_updatePartial('.js-wishlist', html);

  // update events to refresh quantity
  //_updatePartial('.event-single__detail', html);

  // update events to refresh modals
 // _updatePartial('.product-list-modal__form__product-container', html);

}

function _updatePartial(partialSelector, html) {

  let updatedPartial = html.querySelectorAll(partialSelector), i;

  // loop through all partialSelector incase more than one on the page like with wishlist/cart
  for (i = 0; i < updatedPartial.length; ++i) {
    if( typeof(updatedPartial[i]) != 'undefined' && updatedPartial[i] != null ) {
      let oldPartial = document.querySelectorAll(partialSelector);

      if( typeof(oldPartial[i]) != 'undefined' && oldPartial[i] != null ) {
        oldPartial[i].innerHTML = updatedPartial[i].innerHTML;
      }
    }
  };

   /**
   * I tried setting an event for this. It didn't work. I need to come back to it when it's not Friday afternoon.
   */
  let editAddressCheckout = document.querySelectorAll('.js-checkout-address');
  if( typeof(editAddressCheckout) != 'undefined' && editAddressCheckout != null ) {
    editAddressCheckout.forEach( (addressContainer) => {
      new preFillAddress(addressContainer, addressContainer.dataset.addressType);
    });
  }

  return true;
}


document.addEventListener("updated-cart", function(e) { 
  updatePartials();
});

export {
  checkoutFormUpdateAddress,
  checkoutFormDeleteAddress,
  selectProductVariation,
  selectVintage,
}