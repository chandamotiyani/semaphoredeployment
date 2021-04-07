import AjaxForm from '../../utilities/ajax-form';
import AjaxReloadPartial from '../../utilities/AjaxReloadPartial';
import CheckoutAccordions from './CheckoutAccordions';

export default class UpdateCartPaymentSource extends AjaxForm { // Step #1
  handleSuccess(json) {
    new AjaxReloadPartial('.js-update-checkout', function() {
      new CheckoutAccordions('.js-payment-details').open();
    });
  }
};