import AjaxForm from '../../utilities/ajax-form';
import AjaxReloadPartial from '../../utilities/AjaxReloadPartial';
import CheckoutAccordions from './CheckoutAccordions';

export default class UpdateCartBillingShipping extends AjaxForm { // Step #1
  handleSuccess(json) {
    new AjaxReloadPartial('.js-update-checkout', function() {
      new CheckoutAccordions('.js-payment-details').open();
    });
  }
  handleErrors(json) {
    if(typeof json.errors['billingAddress.zipCode'] !="undefined" && json.errors['billingAddress.zipCode'] ) {
      delete json.errors['billingAddress.zipCode'];
    }
    this.addErrorsToFormInputs(json.errors);
  }
};