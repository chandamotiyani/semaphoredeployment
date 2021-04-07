import AjaxForm from '../utilities/ajax-form';
import AjaxReloadPartial from '../utilities/AjaxReloadPartial';
import { CheckoutFormNotification } from '../utilities/notifications/checkout-form';
import validateCreditCardHelper from '../utilities/validate-credit-card';
export default class UpdatePaymentSource extends AjaxForm {
   handleSuccess(json) {
    if(json.success) {
      new CheckoutFormNotification({
        type: 'success',
        message: `Payment Type Added. Updating. Please wait.`,
        appendTo: this.form,
        notificationClass: 'js-msg'
      });

      new AjaxReloadPartial('.js-payment-source');
    }
  }

  handleError(json) {
    if(json.error) {

      let errors = this.validateCreditCard();

      if(errors) {
        this.addErrorsToFormInputs(errors, this.form);
      } else {
        super.handleError(json);
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