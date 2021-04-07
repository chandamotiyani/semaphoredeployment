import AjaxForm from '../../utilities/ajax-form';

export default class UserCreateAccount extends AjaxForm { // Step #1
  handleSuccess(json) {

    // Push event to datalayer - GA tracking
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      'event': 'createaccountcheckout'
    });

    window.location.href += "#account-created";
    location.reload();
    /*
    new AjaxReloadPartial('.js-update-checkout', function() {

			new CheckoutAccordions('.js-address-details').open();
    });
    */
  }
};