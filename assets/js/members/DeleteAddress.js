import AjaxForm from '../utilities/ajax-form';
import AjaxReloadPartial from '../utilities/AjaxReloadPartial';
import { CheckoutFormNotification } from '../utilities/notifications/checkout-form';

export default class DeleteAddress extends AjaxForm {

  setRequestBody() {

    new CheckoutFormNotification({
      type: 'success',
      message: `Deleting Address. Please Wait.`,
      appendTo: this.form,
      notificationClass: 'js-msg'
    });

    return new URLSearchParams( {
      action: 'commerce/customer-addresses/delete',
      id: this.form.querySelector('input[name="id"]').value
    });
  }
   handleSuccess(json) {
    if(json.success) {
      new AjaxReloadPartial('.js-update-address', false, false, function(partial) {
        new CheckoutFormNotification({
          type: 'success',
          message: `Address Deleted.`,
          appendTo: partial,
          notificationClass: 'js-msg'
        });
      });

    }
  }

  handleErrors(json) {

    if(json.errors) {

      let errors = {};
      for (let [key, value] of Object.entries(json.errors)) {

        errors[`address[${key}]`] = value;

      }
      this.addErrorsToFormInputs(errors, this.form);
    }
  }
}