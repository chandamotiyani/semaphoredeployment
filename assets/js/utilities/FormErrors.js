import Jump from 'jump.js';
import { easeInOutQuad } from './easing';

/**
 *  @param errors
 *  @type Object - This must be an {} with the inputs name as key and error as value.
 *
 *  @param form
 *  @type DOM Node - This is the form you want the errors to appear on.
 *
 * In Craft, Error message will either look like this: shipping[firstName] or like this: shipping.firstName
 * The error response gives us a clue of form name, but when the error message is a property 
 * ie. shipping.firstName, the form name will be shipping[firstName], 
 * so we want to check for that as we're (later) trying to select the *element by it's name.
 *
 * Example Input for @param errors:
 * {
 *   firstName: ["First Name cannot be blank."]
 *   lastName: ["Last Name cannot be blank."]
 *   month: ["Month cannot be blank."]
 *   year: ["Year cannot be blank."]
 *   cvv: ["Cvv cannot be blank."]
 *   number: ["Number cannot be blank."]
 *   expiry: "Month cannot be blank. Year cannot be blank."
 * }
 */

export default class FormErrors {

  constructor(form, errors) {
    this.form = form;
    this.errors = errors;
  }

  addErrorsToFormInputs() {

    // clean slate
    this.resetErrors();

    // attach error messages to form inputs
    this.setErrors();

    // Jump to first error
    let firstError = this.form.querySelector('.has-error');
    if( typeof firstError != 'undefined' && firstError != null ) {
      Jump(firstError, {
        duration: 500,
        offset: -330,
        callback: undefined,
        easing: easeInOutQuad,
        a11y: false
      });
    }

    return (typeof firstError != "undefined" && firstError != null)
  }

  resetErrors() {
    //rest errors
    let errorMsgs = document.querySelectorAll('.form__input-error-msg')
    if( typeof(errorMsgs) != 'undefined' && errorMsgs != null ) {
      errorMsgs.forEach( (error) => {
        error.innerHTML = '';
        error.classList.remove('has-error');
      });
    }

    let inputs = this.form.querySelectorAll('.form__input');
    if( typeof(inputs) != 'undefined' && inputs != null ) {
      inputs.forEach( (input) => {
        input.classList.remove('form__input-error-msg');
      });
    }

    let inputElements = this.form.querySelectorAll('.error');
    if( typeof(inputElements) != 'undefined' && inputElements != null ) {
      inputElements.forEach( (inputElement) => {
        inputElement.classList.remove('error');
      });
    }
  }

  setErrors() {

    for (var error in this.errors) {

      let elementName = error;
      if(error.includes('.')) {
        elementName = error.replace('.', '[').concat(']');
      }

      let el = this.form.querySelector(`[name="${elementName}"]`);

      if(typeof el == "undefined" || el == null) {
        el = this.form.querySelector(`[data-error-name="${elementName}"]`);
      }

      if( typeof el != 'undefined' && el != null  ) {

        let errorMessages = '';
        let errorMsgEl = el.parentElement.querySelector('.form__input-error-msg');

        if( typeof(errorMsgEl) != 'undefined' && errorMsgEl != null ) {
          errorMessages = this.errors[error];
          el.classList.add('error');
          errorMsgEl.innerHTML = errorMessages;
          errorMsgEl.classList.add('has-error');
        }
      }
    }
  }
}
