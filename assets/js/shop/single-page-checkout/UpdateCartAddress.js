import AjaxForm from '../../utilities/ajax-form';
import serialize from '../../utilities/serialize';
import AjaxReloadPartial from '../../utilities/AjaxReloadPartial';

export default class UpdateCartAddress extends AjaxForm { // Step #1

  setRequestBody() {
    this.form = document.querySelector('.js-checkout-address');
    let params = new URLSearchParams( serialize(this.form) );
    params.set('action', '/commerce/cart/update-cart');

    return params;
  }

  handleSuccess(json) {

    new AjaxReloadPartial('.js-update-checkout', function() {
      new CheckoutAccordions('.js-payment-details').open();
    }, '.js-personal-details');
  }

  handleError(json) {
  }

  handleErrors(json) {
  }
};