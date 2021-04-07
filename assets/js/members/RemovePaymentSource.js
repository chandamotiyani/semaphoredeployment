import AjaxForm from '../utilities/ajax-form';
import { CheckoutFormNotification } from '../utilities/notifications/checkout-form';

export default class RemovePaymentSource extends AjaxForm {
   handleSuccess(json) {
    if(json.success) {
      new CheckoutFormNotification({
        type: 'success',
        message: `Payment Type Removed.`,
        appendTo: this.form,
        notificationClass: 'js-msg'
      });

      // remove form and option from select.
      let paymentSourceSelect = document.querySelector('[name="paymentsource"]');
      let selected = paymentSourceSelect.options.selectedIndex;
      paymentSourceSelect.options[selected].remove();
      this.form.remove();
    }
  }
};