import AjaxForm from '../utilities/ajax-form';
import { CheckoutFormNotification } from '../utilities/notifications/checkout-form';
import serialize from '../utilities/serialize';
export default class UpdatePaymentForm extends AjaxForm {
  
  setRequestBody() {
    let params = new URLSearchParams( new FormData(this.form) );
    //this.form = document.querySelector('.checkout__row.js-tabbed-panels');

    return params;
  }

  handleError(json) {
    let jsonErrors = json.paymentForm;

    if(jsonErrors) {

      if(jsonErrors.month || jsonErrors.year) { // validation error in response is on year and month, but the input's name is 'expiry' and combines both.
        jsonErrors.expiry = `${jsonErrors.month || ''} ${jsonErrors.year || ''}`;
      }

      this.addErrorsToFormInputs(jsonErrors, this.form);
    } else if(json.error) {
      if(json.error == 'No customer email address exists on this cart.') {
        let jsonErrors = {
          email: 'Email address cannot be blank.',
        };

        this.addErrorsToFormInputs(jsonErrors, document.querySelector('.js-checkout-account-create')); // add the error to the email field
      } else if(json.error != 'There is no shipping method selected for this order.') {
        new CheckoutFormNotification({
          type: 'fail',
          message: `Error: ${json.error}`,
          appendTo: this.form,
          notificationClass: 'js-msg',
        });
      }
    }
  }

  handleSuccess(json) {
    window.location = json.cart.returnUrl;
  }
}