import SigninForm from '../members/SigninForm';
import ForgotPasswordForm from '../members/ForgotPasswordForm';
import SignupForm from '../members/SignupForm';
import UpdateUserForm from '../members/UpdateUserForm';
import DeleteUserForm from '../members/DeleteUserForm';
import ChangePasswordForm from '../members/ChangePasswordForm';
import UpdatePaymentSource from '../members/UpdatePaymentSource';
import RemovePaymentSource from '../members/RemovePaymentSource';
import UpdateAddress from '../members/UpdateAddress';
import preFillAddress from '../shop/pre-fill-address'
import DeleteAddress from '../members/DeleteAddress'

import AjaxForm from '../utilities/ajax-form';

/**
 * User Signin Form
 */
let signinForm = document.querySelectorAll('.js-signin-form');
if( typeof(signinForm) != 'undefined' && signinForm != null ) {

  signinForm.forEach(function(form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      new SigninForm(form);
    });
  });
}

/**
 * Forgot Password Form
 */
let forgotPassword = document.querySelectorAll('.js-forgot-password-form');
if( typeof(forgotPassword) != 'undefined' && forgotPassword != null ) {

  forgotPassword.forEach(function(form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      new ForgotPasswordForm(form);
    });
  });
}

/**
 * Update a User
 */
let updateUser = document.querySelectorAll('.js-update-user-form');
if( typeof(updateUser) != 'undefined' && updateUser != null ) {

  updateUser.forEach(function(form) {
    let emailField = form.querySelector('[name="email"]');
    // emls.forEach(function(eml) {
    let emailFieldOriginalValue = emailField.value;
    
    emailField.addEventListener('change', (e) => {
        //we need to get the password:
      let passwordField = form.querySelector('[name="password"]');
      let wrap = form.querySelector('.password-wrap');
      if(emailFieldOriginalValue !== e.target.value){
        wrap.style.display = "block";
        passwordField.disabled = false;
      }
      else{
        wrap.style.display = "none";
        passwordField.disabled = true;
      }
    });

    form.addEventListener('submit', (e) => {
      e.preventDefault();

      new UpdateUserForm(form);
    });
  });
}

/**
 * Signup during Checkout
 */
let signupCheckout = document.querySelectorAll('.js-checkout-account-create');
if( typeof(signupCheckout) != 'undefined' && signupCheckout != null ) {

  signupCheckout.forEach(function(form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      new SignupForm(form);
    });
  });
}

/**
 * Delete Account
 */
let deleteAccount = document.querySelectorAll('.js-delete-account');
if( typeof(deleteAccount) != 'undefined' && deleteAccount != null ) {
  deleteAccount.forEach(function(form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      let deleteConfirmation = confirm("This will permanently delete your account. Please confirm.");

      if(deleteConfirmation) {
        new DeleteUserForm(form);
      }
    });
  });
}

/**
 * Change Password
 */
let changePassword = document.querySelectorAll('.js-checkout-account-change-password');
if( typeof(changePassword) != 'undefined' && changePassword != null ) {

  changePassword.forEach(function(form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      let password = form.querySelector('input[name="newPassword"]');
      let confirmPassword = form.querySelector('input[name="confirmPassword"]');
      try {
        if(password.value == confirmPassword.value && password.value !="") {
          new ChangePasswordForm(form);
        } else {
          alert('The passwords do not match');
        }
      } catch {}

    });
  });
}


/**
 * Update Payment Source
 */
document.addEventListener('submit', (e) => {
  if(e.target.classList.contains('js-update-payment-source')) {
    e.preventDefault();

    let form = e.target;
    new UpdatePaymentSource(form);
  }
});

/**
 * Remove Payment Source
 */
document.addEventListener('submit', (e) => {
  if(e.target.classList.contains('js-remove-payment-source')) {
    e.preventDefault();

    let form = e.target;
    new RemovePaymentSource(form);
  }
});

/**
 * Update Address
 */
document.addEventListener('submit', (e) => {
  if(e.target.classList.contains('js-update-address')) {
    e.preventDefault();

    let form = e.target;
    new UpdateAddress(form);
  }
});

/**
 * Delete Address
 */
document.addEventListener('click', (e) => {

 if(e.target.classList.contains('js-delete-address')) {
   e.preventDefault();
   let form = document.querySelector('.js-update-address');
   new DeleteAddress(form, '', '/commerce/customer-addresses/delete');
 }

});
/**
 * Prefill Address
 */
let editAddressCheckout = document.querySelectorAll('.js-checkout-address');
if( typeof(editAddressCheckout) != 'undefined' && editAddressCheckout != null ) {
  editAddressCheckout.forEach( (addressContainer) => {
    new preFillAddress(addressContainer, addressContainer.dataset.addressType, 'false');
  });
}
/**
 * Generic
 */
let ajaxForm = document.querySelectorAll('.js-ajax-form');
if( typeof(ajaxForm) != 'undefined' && ajaxForm != null ) {

  ajaxForm.forEach(function(form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      new AjaxForm(form);
    });
  });
}