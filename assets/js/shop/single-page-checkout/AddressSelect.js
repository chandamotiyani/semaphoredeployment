import AjaxForm from '../../utilities/ajax-form';
import AjaxReloadPartial from '../../utilities/AjaxReloadPartial';
import CheckoutAccordions from './CheckoutAccordions';

export default class AddressSelect extends AjaxForm { // Step #1
  handleSuccess(json) {
    new AjaxReloadPartial('.js-update-checkout', function() {
      new CheckoutAccordions('.js-address-details', false).open();
    });
  }
};