import { CheckoutFormNotification } from './notifications/checkout-form';
import FormErrors from '../utilities/FormErrors';
import 'whatwg-fetch'; 

export default class AjaxForm {

  constructor(form, callback, action=window.location.origin, loadingClass='loading') {
    this.action = action;
    this.loadingClass=loadingClass;
    this.form = form;
    this.requestBody = this.setRequestBody();
    this.doAjax();
    this.callback = callback;
  }

  setRequestBody() {

    if(this.form.querySelector('input[type="file"]')) {
      return new FormData(this.form);
    } else {
      let formData = new FormData(this.form);
      return new URLSearchParams(formData);
    }
  }

  async doAjax() {
    let json = {};

    try {
      if(this.loadingClass) {
        this.form.classList.add(this.loadingClass);
      }
    } catch {}

    let submitBtn = this.form.querySelector('input[type="submit"]');

    if(typeof submitBtn !=null && typeof submitBtn !="undefined") {
      try {
        submitBtn.classList.add('is-busy');
        submitBtn.disabled = true;
      } catch {}
    }

    // clear old messages
    let submitMsg = this.form.querySelector('.js-msg');

    if(typeof(submitMsg) != 'undefined' && submitMsg != null) {
      submitMsg.classList.remove('js-msg--success');
      submitMsg.classList.remove('js-msg--fail');
    }
    try {
      // do ajax
      const response = await fetch(this.action, { // uses the action input in the requestBody
        method: 'POST',
        body: this.requestBody,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-Token': window.csrfTokenValue,
        },
      });

      json = await response.json();
      if(this.callback && {}.toString.call(this.callback) === '[object Function]') {
        this.callback(json);
      }

    } catch(e) {
      //this.form.submit();
    }

    // remove loading state
    try {
      if(this.loadingClass) {
        this.form.classList.remove(this.loadingClass);
      }
    } catch {}

    try {
      submitBtn.classList.remove('is-busy');
      submitBtn.disabled = false;
    } catch {}

    if(json.success) {
      this.handleSuccess(json)
    }

    if(json.errors) {
      this.handleErrors(json)
    } else if(json.error) {
      this.handleError(json)
    }
  }

  handleSuccess(json) {
    new CheckoutFormNotification({
      type: 'success',
      message: 'Your form was submitted',
      appendTo: this.form,
      notificationClass: 'js-msg'
    });
  }

  handleErrors(json) {
    let errors = this.addErrorsToFormInputs(json.errors);
    if(! errors ) {
      if(json.error) {
        this.handleError(json);
      }
    }
  }

  handleError(json) {
    var errorMessage = `Error: ${json.error}`;
    if(json.error && json.errors){
      errorMessage += `<ul>`;
      Object.keys(json.errors).forEach(function(key,index) {
        json.errors[key].forEach(function(arkey, arind){
          errorMessage += `<li>${arkey}</li>`;
        });
      });
      errorMessage += `</ul>`;
    }
    new CheckoutFormNotification({
      type: 'fail', 
      message: errorMessage,
      appendTo: this.form,
      notificationClass: 'js-msg',
    });
  }

  addErrorsToFormInputs(json, form = this.form) {
    let Errors = new FormErrors(form, json);

    return Errors.addErrorsToFormInputs();
  }
}

