import AjaxForm from '../utilities/ajax-form';
import { CheckoutFormNotification } from '../utilities/notifications/checkout-form';

export default class DeleteUserForm extends AjaxForm {

  handleSuccess(json) {

    if(json.success) {
      new CheckoutFormNotification({
        type: 'success',
        message: `User deleted. Redirecting..`,
        appendTo: this.form,
        notificationClass: 'js-msg'
      });
    }

    window.location = '/';
  }
};