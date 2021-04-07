import AjaxForm from '../utilities/ajax-form';
import { CheckoutFormNotification } from '../utilities/notifications/checkout-form';

export default class UpdateUserForm extends AjaxForm {

  handleSuccess(json) {

    if(json.success) {
      new CheckoutFormNotification({
        type: 'success',
        message: `Your contact details have been updated`,
        appendTo: this.form,
        notificationClass: 'js-msg'
      });
    }
  }

  handleErrors(json) {
    let errors = this.addErrorsToFormInputs(json.errors);
    if(! errors ) {
      if(json.errors) {
        this.handleError(json);
      }
    }
  }

  handleError(json) {
    // TODO: we should probably validate the 1st name and last name etc in here too.
    let message = json.errors;
    if(json.errors.currentPassword[0]== "Incorrect current password."){
      message = "You must enter your current password to change your email address."
    }

    new CheckoutFormNotification({
      type: 'fail',
      message: "Error: " + message,
      appendTo: this.form,
      notificationClass: 'js-msg',
    });
  }

};