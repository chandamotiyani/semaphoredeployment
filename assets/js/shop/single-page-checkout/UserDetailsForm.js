import AjaxForm from '../../utilities/ajax-form';
import AjaxReloadPartial from '../../utilities/AjaxReloadPartial';
import Jump from 'jump.js';
import { easeInOutQuad } from '../../utilities/easing';
import CheckoutAccordions from './CheckoutAccordions';
import FormErrors from '../../utilities/FormErrors';
import moment from 'moment';

export default class UserDetailsForm extends AjaxForm { // Step #1
  handleSuccess(json) {

		let errors = {};
		let phone = this.form.querySelector('[name="fields[phoneNumber]"]');
		let firstName = this.form.querySelector('[name="fields[firstName]"]');
		let lastName = this.form.querySelector('[name="fields[lastName]"]');

		if(typeof firstName !=="undefined" && firstName !==null) {
			if(firstName.value == "") {
				errors['fields[firstName]'] = 'First name is required';
			}
		}

		if(typeof lastName !=="undefined" && lastName !==null) {
			if(lastName.value == "") {
				errors['fields[lastName]'] = 'Last name is required';
			}
		}

		if(typeof phone !=="undefined" && phone !==null) {
			if(phone.value.length > 20) {
				errors['fields[phoneNumber]'] = 'Invalid phone number';
			}
			if(phone.value == "") {
				errors['fields[phoneNumber]'] = 'Phone number is required';
			}
		}

		let dateOfBirth = this.form.querySelector('[name="fields[dateOfBirth]"]');
		if(typeof dateOfBirth !=="undefined" && dateOfBirth !==null) {
			if(dateOfBirth.value == "") {
				errors['fields[dateOfBirth]'] = 'Date of Birth cannot be empty.';
			}

			let m = moment(dateOfBirth.value, 'DD/MM/YYYY');

			const age = moment().diff(m, 'years');

			if(isNaN(age) || !age) {
				errors['fields[dateOfBirth]'] = 'Date of birth is invalid';
			}

			if(age < 18) {
				errors['fields[dateOfBirth]'] = 'You must be 18 years old or over.';
			}

			if(age > 120) {
				errors['fields[dateOfBirth]'] = 'Date of birth is invalid';
			}

			//double check it IS a valid date in the format we want before we continue.
			let validateDate = new RegExp(/^(0?[1-9]|[12][0-9]|3[01])[\/](0?[1-9]|1[012])[\/]\d{4}$/);
			if(validateDate.test(dateOfBirth.value) == false) {
				errors['fields[dateOfBirth]'] = 'Date of birth is invalid';
			}
		}


		if(Object.keys(errors).length) {
			let Errors = new FormErrors(this.form, errors);

			Errors.addErrorsToFormInputs();

			return;
		}


		if( typeof document.querySelector('.js-user-create-account') !="undefined" && document.querySelector('.js-user-create-account') !=null ) {
				new AjaxReloadPartial('.js-update-checkout', function() {
						new CheckoutAccordions('.js-personal-details', false).open();

						document.querySelector('.js-user-create-account').classList.add('visible');
						Jump(document.querySelector('.js-user-create-account'), {
							duration: 300,
							offset: -100,
							callback: undefined,
							easing: easeInOutQuad,
							a11y: false
						});
				}, '.js-personal-details')
			} else {
				new AjaxReloadPartial('.js-update-checkout', function() {
					// if the option to create an account is present, show the create account.
						new CheckoutAccordions('.js-address-details').open();
				}, '.js-personal-details');
			}
  }
};

/**
 * TODO: Create accordion JS script
 */