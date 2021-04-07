import SigninForm from './SigninForm';
import UpdateCartEmail from './UpdateCartEmail';
import UserDetailsForm from './UserDetailsForm';
import UserCreateAccount from './UserCreateAccount';
import UpdateCartBillingShipping from './UpdateCartBillingShipping';
import UpdateCartPaymentSource from './UpdateCartPaymentSource';
import CheckoutFormSubmit from './CheckoutFormSubmit';
import AddressSelect from './AddressSelect';
import CheckoutAccordions from './CheckoutAccordions';
import { CheckoutFormNotification } from '../../utilities/notifications/checkout-form';
import UpdateCartDiscountCode from './UpdateCartDiscountCode';

import Jump from 'jump.js';
import { easeInOutQuad } from '../../utilities/easing';


let checkEmail = document.querySelector('.js-update-cart-email-address');
if( typeof(checkEmail) != 'undefined' && checkEmail != null ) {

}

document.addEventListener('submit', (e) => {

  if(e.target.classList.contains('js-update-cart-email-address')) {
    e.preventDefault();
    new UpdateCartEmail(e.target)
  }

  if(e.target.classList.contains('js-checkout-user-sign-in')) {
    e.preventDefault();
    new SigninForm(e.target)
  }

  if(e.target.classList.contains('js-update-cart-user-details')) {
    e.preventDefault();
    new UserDetailsForm(e.target)
  }

  if(e.target.classList.contains('js-user-create-account')) {
    e.preventDefault();
    new UserCreateAccount(e.target)
  }

  if(e.target.classList.contains('js-update-cart-billing-shipping')) {
    e.preventDefault();
    new UpdateCartBillingShipping(e.target)
  }

  if(e.target.classList.contains('js-payments-pay-checkout-form')) {
    e.preventDefault();
    new CheckoutFormSubmit(e.target)
  }


  if(e.target.classList.contains('js-update-cart-discount-code')) {
    e.preventDefault();
    new UpdateCartDiscountCode(e.target)
  }
});

document.addEventListener('change', (e) => {

  /**
   * Update Payment Source
   */
  if(e.target.classList.contains('js-update-cart-payment-source')) {
    e.preventDefault();
    new UpdateCartPaymentSource(e.target.form)
  }


  /**
   * Select Address

  if(e.target.classList.contains('js-update-cart-address-select')) {
    e.preventDefault();
    // Clear form fields if no address has been supplied.
    if(e.target.value == "") {
      if(e.target.name == 'billingAddressId') {
        e.target.form.querySelectorAll('.checkout__form-address--billing input, .checkout__form-address--billing select').forEach(function(field) {
          field.disabled = false;
          field.value = "";
        });
      }
      if(e.target.name == 'shippingAddressId') {
        e.target.form.querySelectorAll('.checkout__form-address--shipping input, .checkout__form-address--shipping select').forEach(function(field) {
          field.disabled = false;
          field.value = "";
        });
      }


      //e.target.form.reset();
    } else {
      new AddressSelect(e.target.form)
    }
  }

     */
});


document.addEventListener('click', (e) => {

   /**
   * Skip creating account
   */
  if(e.target.classList.contains('js-sign-in-continue')) {
    e.preventDefault();
    new CheckoutAccordions('.js-address-details').open();
  }

   /**
   * Skip Sign in and continue as guest.
   */
  if(e.target.classList.contains('js-sign-in-continue-guest')) {
    e.preventDefault();

    Jump(document.querySelector('.js-update-cart-user-details'), {
			duration: 800,
			offset: -80,
			callback: undefined,
			easing: easeInOutQuad,
			a11y: false
		})
  }

  /**
   * Edit closed accordions.
   */
  if(e.target.classList.contains('accordion-tab__edit-link')) {
    e.preventDefault();
    e.target.parentElement.parentElement.classList.add('accordion-tab--active');
  }
});

if(window.location.hash == '#account-created') {
  let personalDetails = document.querySelector('.js-personal-details')
  personalDetails.classList.add('accordion-tab--active');

  let successMsgAnchor = personalDetails.querySelector('.signed-up-msg');

  new CheckoutFormNotification({
    type: 'success',
    message: `Your account has been created.`,
    appendTo: successMsgAnchor,
    notificationClass: 'js-msg'
  });
}

if(window.location.hash == '#signed-in') {
  new CheckoutAccordions('.js-personal-details').open();
}