import AjaxForm from '../utilities/ajax-form';
import { actions } from '../store';

export default class SigninForm extends AjaxForm {
  async handleSuccess(json) {
    if(window.product) {
      window.csrfTokenValue = json.csrfTokenValue; // Update CSRF token
      await actions.updateCart(window.product, 'panel');
      window.location = window.location.href.split('#')[0]+"#cart-summary"; // Add hash to open slide out
      window.location = window.location.href.split('#')[0]; // reload the page without the hash
    } else if(window.location.hash == '#wishlist' || window.location.hash == '#cart-summary') {
      window.location = '/members/my-membership';
    } else {
      location.reload(true);
    }
  }
};