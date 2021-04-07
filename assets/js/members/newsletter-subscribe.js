import FormErrors from '../utilities/FormErrors';

document.addEventListener('submit', (e) => {
  if(e.target.classList.contains('js-subscribe-form')) {
    e.preventDefault();

    let form = e.target;
    let email = form.querySelector('[name="Email Address"]').value;

    if(!/\S+@\S+\.\S+/.test(email)) { // invalid email

      let msg = '';
      if(email == "") {
        msg = 'Email can not be empty';
      } else {
        msg = 'Invalid Email';
      }

      let Errors = new FormErrors(form, {
        'Email Address': msg
      });

      Errors.addErrorsToFormInputs();

      return false;
    }

    sessionStorage.setItem("email", email);

    // Push event to datalayer - GA tracking
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      'event': 'mailinglistsubscribe'
    });

    // submit form
    form.submit();
  }
});

let email = sessionStorage.getItem("email");
if(email) {
  let emailInput = document.querySelector('.js-checkout-account-create [name="email"]');

  if(typeof emailInput !="undefined" && emailInput !=null) {
    emailInput.value = email;
  }

  sessionStorage.clear('email');
}

if(location.href.includes("errorcode=8")) {
  location = location.href.split('?')[0];

  let form = document.getElementById(location.hash.substring(1));

  let Errors = new FormErrors(form, {
    'Email Address': 'This email address is already subscribed'
  });

  Errors.addErrorsToFormInputs();
}

