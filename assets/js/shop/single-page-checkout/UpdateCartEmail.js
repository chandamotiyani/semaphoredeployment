import AjaxForm from '../../utilities/ajax-form';
import AjaxReloadPartial from '../../utilities/AjaxReloadPartial';
import CheckoutAccordions from './CheckoutAccordions';

export default class UpdateCartEmail extends AjaxForm { // Step #1

  handleSuccess(json) {

    if(json.cart.email == '') {
      this.addErrorsToFormInputs({ email: 'Email Address is Required' });

      return;
    }

    new AjaxReloadPartial('.js-update-checkout', function() {
      new CheckoutAccordions('.js-personal-details').open();
    }, '.js-update-cart-email-address');
  }
};