import AjaxForm from '../../utilities/ajax-form';
import { CheckoutFormNotification } from '../../utilities/notifications/checkout-form';
import validateCreditCardHelper from '../../utilities/validate-credit-card';

export default class CheckoutFormSubmit extends AjaxForm {
  handleSuccess(json) {

    if(json.cart.returnUrl) {
      setTimeout(function(){
        window.location = json.cart.returnUrl;
      }, 1000);
    } else {
      new CheckoutFormNotification({
        type: 'fail',
        message: `Your order was complete, but the website could not redirect you. Please check your email for confirmation of your order.`,
        appendTo: document.querySelector('.js-checkout-submit'),
        notificationClass: 'js-msg',
      });
    }
  }

  handleError(json) {
    let jsonErrors = json.paymentFormErrors;
    try {
      if(typeof jsonErrors !=="undefined" && jsonErrors !==null && Object.keys(jsonErrors).length) {

        if(jsonErrors.month || jsonErrors.year) { // validation error in response is on year and month, but the input's name is 'expiry' and combines both.
          jsonErrors.expiry = `${jsonErrors.month || ''} ${jsonErrors.year || ''}`;
        }

        if(jsonErrors.firstName || jsonErrors.lastName) { // validation error in response is on first and last name, but the input's name is 'fullName' and combines both.
        jsonErrors.fullName = `${jsonErrors.firstName || ''} ${jsonErrors.lastName || ''}`;
      }


        this.addErrorsToFormInputs(jsonErrors, this.form);
      } else if(Object.keys(json.orderErrors).length) {
        if(json.orderErrors.eventBooking){
          new CheckoutFormNotification({
            type: 'fail',
            message: `Error: ${json.orderErrors.eventBooking}`,
            appendTo: this.form,
            notificationClass: 'js-msg',
          });
        } else {
          new CheckoutFormNotification({
            type: 'fail',
            message: ``,
            appendTo: this.form,
            notificationClass: 'js-msg',
          });
        }
      } else if(json.error) {
        new CheckoutFormNotification({
          type: 'fail',
          message: `Sorry, your payment could not be processed. Please try another card or try again later.`,
          appendTo: this.form,
          notificationClass: 'js-msg',
        });
      }
    } catch(e) {

      // saving cc doesn't get validated by spreedly and returns a generic server error so we end up here. First look for invalid CC info.
      let errors = this.validateCreditCard();

      if(errors) {
        this.addErrorsToFormInputs(errors, this.form);
      } else {
        // default to generic payment error.
        new CheckoutFormNotification({
          type: 'fail',
          message: `Payment Error`,
          appendTo: this.form,
          notificationClass: 'js-msg',
        });

      }
    }
  }

  validateCreditCard() {

    let errors = {};

    const cardNumber = document.getElementById('checkout-form-number').value;
    const expiry = document.getElementById('checkout-form-expiry').value;
    const cvv = document.getElementById('checkout-form-cvv').value;
    const firstName = document.getElementById('checkout-form-firstName-').value;
    const lastName = document.getElementById('checkout-form-lastName-').value;

    if(lastName == "") {
      errors[`fullName`] = 'Last Name cannot be blank';
    }

    if(firstName == "") {
      errors[`fullName`] = 'Name cannot be blank';
    }

    if(validateCreditCardHelper.invalidCard(cardNumber)) {
      errors[`number`] = 'Invalid Credit Card Number';
    }

    if(validateCreditCardHelper.invalidExp(expiry)) {
      errors[`short_expiry`] = validateCreditCardHelper.invalidExp(expiry);
    }

    if(validateCreditCardHelper.invalidCVV(cvv)) {
    //  errors[`cvv`] = this.invalidCVV(cvv);
    }

    return errors;
  }
};