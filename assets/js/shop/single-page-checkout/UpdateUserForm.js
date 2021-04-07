import AjaxForm from '../../utilities/ajax-form';
import serialize from '../../utilities/serialize';

export default class UpdateUserForm extends AjaxForm {

  setRequestBody() {

    let userDetailsForm = document.querySelector('.js-checkout-account-create');
    this.form = userDetailsForm;
    let params = new URLSearchParams( new FormData(userDetailsForm) );
    params.delete('password');
    params.set('action', 'users/save-user');
    params.set('userId', window.globals.userId);

    return params;
  }

  handleSuccess() {}

  handleError() {}

  handleErrors() {}
};