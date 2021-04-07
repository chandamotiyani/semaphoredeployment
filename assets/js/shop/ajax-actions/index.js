/**
 * TODO: REFACTOR IN PROGRESS. ::: Refactor is currently about 70% done. We now have AjaxForms and AjaxReloadPartial classes as well as Vue JS components - This file will be NO MORE before launch, please don't add to it.
 */

import {
  //removeFromWishlist,
  selectProductVariation,
  selectVintage,
  //checkoutFormUpdateAddress,
  checkoutFormDeleteAddress
} from './actions';

import 'whatwg-fetch'; 

/**
 * Billing same as shipping toggle
 */
document.addEventListener('change', (e) => {
  window.es = e;
  if(e.target.name == 'billingAddressSameAsShipping') {
    e.preventDefault();
    document.querySelector('.js-toggle-address').classList.toggle('active', !e.target.checked );
  }
});

/**
 * Dropdown Panel for SignIn Checkout
 */
let toggleSignIn = document.querySelector('.js-checkout-sign-in');
if( typeof(toggleSignIn) != 'undefined' && toggleSignIn != null ) {
  toggleSignIn.addEventListener("click", (e) => {
    e.preventDefault();
    document.querySelector('.panel-collapse.js-signin-form').classList.toggle('active');
  });
}

/**
 * Prefill shipping address Name fields for new users.
 */
let firstNameField = document.getElementsByName('firstName')[0];
let lastNameField = document.getElementsByName('lastName')[0];
let shippingFirstNameField =document.getElementsByName('shippingAddress[firstName]')[0];
let shippingLastNameField =document.getElementsByName('shippingAddress[lastName]')[0];

if( typeof(firstNameField) != 'undefined' && firstNameField != null ) {
  firstNameField.addEventListener("change", (e) => {
    if( typeof shippingFirstNameField !="undefined" && shippingFirstNameField.value == '') {
      shippingFirstNameField.value = e.target.value;
    }
  });
}

if( typeof(lastNameField) != 'undefined' && lastNameField != null ) {
  lastNameField.addEventListener("change", (e) => {
    if( typeof shippingLastNameField !="undefined" && shippingLastNameField.value == '') {
      shippingLastNameField.value = e.target.value;
    }
  });
}

/**
 * Checkout - Add new Card
 */
let paymentSourceDropdown = document.getElementsByName('paymentSourceId')[0];
if( typeof(paymentSourceDropdown) != 'undefined' && paymentSourceDropdown != null ) {
  paymentSourceDropdown.addEventListener("change", (e)=>{
    let paymentForm = document.querySelectorAll('.js-payment-form');

    if( paymentSourceDropdown.value === '') {
      //we are adding a new card - show the forms
      for (let i = 0; i < paymentForm.length; i++) {
        paymentForm[i].classList.remove('hidden');
      }
    }
    else{
      for (let i = 0; i < paymentForm.length; i++) {
        paymentForm[i].classList.add('hidden');
      }
    }
  });
};





let debounce = null;

/**
 * Select payment source
 */
document.addEventListener('change', (e) => {
  if(e.target.name == 'paymentsource') {
    e.preventDefault();

    document.querySelector('.add-new-payment-type').classList.add('hidden');

    if(e.target.value == 'add') {
      document.querySelector('.add-new-payment-type').classList.remove('hidden');
    }

    let gatewayFields = document.querySelectorAll('.payment-source');
    if( typeof gatewayFields !="undefined") {
      gatewayFields.forEach((field) => {
        field.classList.add('hidden');
      });
    }

    let gateway = document.getElementById(`payment-source-${e.target.value}`);
    if(gateway) {
      gateway.classList.remove('hidden');
    }
  }
});

/**
 * Select payment gateway
 */
document.addEventListener('change', (e) => {
  if(e.target.name == 'select-payment') {
    e.preventDefault();

    let gatewayFields = document.querySelectorAll('.gateway-fields');
    if( typeof gatewayFields !="undefined") {
      gatewayFields.forEach((field) => {
        field.classList.add('hidden');
      });
    }

    let gateway = document.getElementById(`fields-${e.target.value}`);
    if(gateway) {
      gateway.classList.remove('hidden');
    }
  }
});

document.addEventListener('click', (e) => {
  if(e.target.classList.contains('js-product-cart-summary-remove')) {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
      addItem(e.target.closest('form'))
    }, 500);
  }
});

/**
 * Select a Product Variant
 */
document.addEventListener('click', (e) => {
  if(e.target.classList.contains('js-product-variant')) {
    e.preventDefault();
    let productId = e.target.dataset.productId;
    let disableBuyButton = e.target.dataset.disableBuyButton;
    selectProductVariation(e.target.href, productId, disableBuyButton);
  }
});

/**
 * Select a Product Variant - mobile
 */
document.addEventListener('change', (e) => {
  if(e.target.classList.contains('js-product-variant-select')) {
    e.preventDefault();
    let productId = e.target.options[e.target.selectedIndex].dataset.productId;
    let disableBuyButton = e.target.options[e.target.selectedIndex].dataset.disableBuyButton;
    selectProductVariation(e.target.value, productId, disableBuyButton);
  }
});


document.addEventListener('change', (e) => {

  if(typeof e.srcElement.form == 'undefined' || !e.srcElement.form) {
    return;
  }

  if(e.srcElement.form.classList.contains('js-product-list-modal-form')) {
    let select = e.srcElement.form.querySelector('[name="purchasableId"]');
    let purchasableid = select.options[select.selectedIndex].value;
    let disableBuyButton = select.options[select.selectedIndex].dataset.disableBuyButton;
    let variantModalUpdated = new CustomEvent("update-add-to-cart-props", {
      "detail": {
        purchasableid: purchasableid,
        productid: e.srcElement.form.dataset.productId,
        disableAttr: disableBuyButton,
      }
    });
    // Dispatch Event
    document.dispatchEvent(variantModalUpdated);

    // Update Image and price
    updateProductListingModal(purchasableid, e.srcElement.form);
  }
});

async function updateProductListingModal(purchasableid, form) {

  const updateProductListModal = await fetch(`/products/variants/${purchasableid}`, {
    method: 'GET',
    headers: {
      "Content-Type": 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
      'X-CSRF-Token': window.csrfTokenValue,
    },
  });

  let json = await updateProductListModal.json();

  if(json.error) {
    let error = json.error
    new Notification({ type: 'error', message: error });
  } else {

    form.querySelector('.product-list-modal__form__price').innerHTML = json[0].price;

    form.querySelector('.product-list-modal__form__image').src = json[0].image;
  }
}

document.addEventListener('change', (e) => {
  if(e.target.classList.contains('js-vintages-family')) {
    e.preventDefault();
    if(e.target.value !="") {
      selectVintage(e.target.value);
    }
  }
});


