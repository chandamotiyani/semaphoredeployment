import AjaxForm from '../utilities/ajax-form';
import { CheckoutFormNotification } from '../utilities/notifications/checkout-form';

export default class ChangePasswordForm extends AjaxForm {
  handleErrors(json) {

    if(json.errors) {
      window.errors = json.errors;
      if(typeof json.errors.currentPassword !="undefined") {
        json.errors.password = json.errors.currentPassword[0];
      }

      this.addErrorsToFormInputs(json.errors, this.form);
    }
  }

  handleSuccess(json) {

    if(json.success) {
      new CheckoutFormNotification({
        type: 'success',
        message: `Your password has been changed`,
        appendTo: this.form,
        notificationClass: 'js-msg'
      });
      window.scrollTo(0,0); 
      location.reload();
    }
  }
};