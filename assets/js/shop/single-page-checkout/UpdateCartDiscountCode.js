import AjaxForm from '../../utilities/ajax-form';
import AjaxReloadPartial from '../../utilities/AjaxReloadPartial';
import CheckoutAccordions from './CheckoutAccordions';
import { actions } from '../../store';

export default class UpdateCartDiscountCode extends AjaxForm { // Step #1
  handleSuccess(json) {
    new AjaxReloadPartial('.js-update-checkout', function() {
     actions.getCart(); // refresh cart summary
      new CheckoutAccordions('.js-payment-details').open();
    });
  }
};