import AjaxForm from '../../utilities/ajax-form';
import serialize from '../../utilities/serialize';
import { CheckoutFormNotification } from '../../utilities/notifications/checkout-form';
import FormErrors from '../../utilities/FormErrors';

export default class UpdateCartForm extends AjaxForm { // Step #1

  setRequestBody() {
    this.form = document.querySelector('.checkout__row.js-tabbed-panels');
    let params = new URLSearchParams( serialize(this.form) );
    params.set('action', '/commerce/cart/update-cart');

    return params;
  }
  handleSuccess(json) {
    if(json.cart.shippingMethod == null) {
        // no shipping methods are available for this order. We'd get a notification when paying anyway, but better UX to do something here.
        let SetErrors = new FormErrors(this.form, {
          'shippingAddress.zipCode': ['Shipping isn\'t available for this postcode.']
        });
        SetErrors.addErrorsToFormInputs();
    }
  }
  handleError(json) {
    new CheckoutFormNotification({
      type: 'fail', 
      message: `Error: ${json.error}`,
      appendTo: document.querySelector('.js-checkout-submit'),
      notificationClass: 'js-msg',
    });
  }
};