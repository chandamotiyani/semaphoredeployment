import AjaxForm from '../utilities/ajax-form';
import { CheckoutFormNotification } from '../utilities/notifications/checkout-form';

export default class ForgotPasswordForm extends AjaxForm {
   handleSuccess(json) {

    if(json.success) {
      new CheckoutFormNotification({
        type: 'success',
        message: `Password reset email sent, please check your email.`,
        appendTo: this.form,
        notificationClass: 'js-msg'
      });
    }
  }
};