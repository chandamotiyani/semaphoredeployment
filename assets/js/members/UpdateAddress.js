import AjaxForm from '../utilities/ajax-form';
import AjaxReloadPartial from '../utilities/AjaxReloadPartial';
import { CheckoutFormNotification } from '../utilities/notifications/checkout-form';

export default class UpdateAddress extends AjaxForm {
   handleSuccess(json) {
    if(json.success) {
      new CheckoutFormNotification({
        type: 'success',
        message: `Updating Address. Please Wait.`,
        appendTo: this.form,
        notificationClass: 'js-msg'
      });

      new AjaxReloadPartial('.js-update-address', false, false, function(partial) {
        new CheckoutFormNotification({
          type: 'success',
          message: `Address Updated.`,
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