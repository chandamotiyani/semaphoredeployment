import AjaxForm from '../utilities/ajax-form';
import { CheckoutFormNotification } from '../utilities/notifications/checkout-form';

export default class SignupForm extends AjaxForm {
   handleSuccess(json) {

    if(json.success) {

      let message = 'You have successfully created an account. Redirecting you to The Wine Club.';

      // Check if it's the wine room page. Wineroom pages don't redirect to wineclub.
      if( document.body.classList.contains('page-slug-wine-room-signup') ) {
        message = 'You have successfully created an account.';
      }

      new CheckoutFormNotification({
        type: 'success',
        message: message,
        appendTo: this.form,
        notificationClass: 'js-msg'
      });

      // Push event to datalayer - GA tracking
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        'event': 'createaccount'
      });

      setTimeout(function(){
        location = window.location.href
      }, 1500);
      
    }
  }
};