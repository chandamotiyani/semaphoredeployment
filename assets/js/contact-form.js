import AjaxForm from './utilities/ajax-form';

export default class ContactForm extends AjaxForm {


  handleSuccess(json) {
    if(json.success) {
      location = json.returnUrl;
    }
  }
};


/**
 * Update Payment Source
 */
let contactForm = document.querySelectorAll('.js-contact-form form');
if( typeof(contactForm) != 'undefined' && contactForm != null ) {

  contactForm.forEach(function(form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      new ContactForm(form);
    });
  });
}