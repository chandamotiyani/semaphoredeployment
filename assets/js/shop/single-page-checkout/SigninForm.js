import AjaxForm from '../../utilities/ajax-form';
import AjaxReloadPartial from '../../utilities/AjaxReloadPartial';
import CheckoutAccordions from './CheckoutAccordions';

export default class SigninForm extends AjaxForm { // Step #1
  handleSuccess(json) {
    window.location.href += "#signed-in";
    location.reload();
  }
};