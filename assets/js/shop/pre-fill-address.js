

export default class preFillAddress {
  constructor(addressForm, addressKey, disableInputs=false) {
    this.addressForm = addressForm;
    this.addressKey = addressKey ? `${addressKey}Address` : 'address';
    this.disableInputs = disableInputs;
    this.addressSelect = this.addressForm.querySelector('.address-select');
  }

  prefillAddress() {

    // Un-disable form fields
    this.addressForm.querySelectorAll('select, input').forEach(function(field) {
      field.disabled = false
    });

    // Reset form
    /*
    this.addressForm.querySelector('.checkout__form-address-actions').classList.remove('checkout__form-address-actions--visible');
    this.addressForm.classList.remove('checkout__form--prefilled');
*/
    /**
     * Clear all fields on new address select and return
     */
    if( this.addressSelect.options[this.addressSelect.selectedIndex].value == '' ) {
      this.clearFields();
      return;
    }


    // Change submit button text
    if(typeof this.addressForm.querySelector('.submit') !=="undefined" && this.addressForm.querySelector('.submit') != null) {
      this.addressForm.querySelector('.submit').value = 'Update Address';
    }

    // hide delete address list.
    if(typeof this.addressForm.querySelector('.js-delete-address') !=="undefined" && this.addressForm.querySelector('.js-delete-address') != null) {
      this.addressForm.querySelector('.js-delete-address').classList.remove('hidden');
    }

    /**
     * Prefill address
     * Disable inputs
     */
    /*
    this.addressForm.classList.add('checkout__form--prefilled');
    this.addressForm.querySelector('.checkout__form-address-actions').classList.add('checkout__form-address-actions--visible');
    */

    // disable 
      if(this.disableInputs && typeof this.addressForm.querySelector('.checkout__form-address') !=="undefined" && this.addressForm.querySelector('.checkout__form-address') !==null) {
        this.addressForm.querySelector('.checkout__form-address').querySelectorAll('select:not(.address-select), select:not(.address-select), input').forEach(function(field) {
          field.disabled = true
        });
      }


      let addressFieldsObj = JSON.parse( JSON.parse(JSON.stringify(this.addressSelect.options[this.addressSelect.selectedIndex].dataset.address)) );

      // loop through address and populate values
      for (var item in addressFieldsObj[0]) {
        let addressField = document.getElementsByName(`${this.addressKey}[${item}]`)[0];
        if( typeof addressField != "undefined" ) {
          addressField.value = addressFieldsObj[0][item];

          if( addressField.type == 'checkbox' ) {
          addressFieldsObj[0][item] ? addressField.checked = 'checked' : addressField.checked = false;
          }

          if( addressField.type == 'select-one' ) {
            window.addresField = addressField;
            /*
            addressField.options.forEach(function(option) {
              if (option.value == addressFieldsObj[0][item]) {
                option.selected = true;
              }
            });
            */
          }
        }
      }

      //update address ID for billing shipping
      let addressId = document.querySelector(`[name="${this.addressKey}Id"]`);
      if( typeof addressId != "undefined" && addressId !=null) {
        addressId.value = addressFieldsObj[0].id;
      }

      //update address ID for deleting
      let deleteAddressId = document.querySelector(`[name="id"]`);
      if( typeof deleteAddressId != "undefined" && deleteAddressId !=null) {
        deleteAddressId.value = addressFieldsObj[0].id;
      }

       //update address ID for customer address
      let singleAddressId = document.querySelector(`[name="address[id]"]`);
      if( typeof singleAddressId != "undefined" && singleAddressId !=null) {
        singleAddressId.value = addressFieldsObj[0].id;
      }

  }

  clearFields() {

    if(typeof this.addressForm.querySelector('.submit') !=="undefined" && this.addressForm.querySelector('.submit') !==null) {
      this.addressForm.querySelector('.submit').value = 'Add Address';
    }

    if(typeof this.addressForm.querySelector('.js-delete-address') !=="undefined" && this.addressForm.querySelector('.js-delete-address') !==null) {
      this.addressForm.querySelector('.js-delete-address').classList.add('hidden');
    }

    let fields = this.addressForm.querySelectorAll('input:not(.submit), select');

    if(typeof fields !=="undefined") {
      fields.forEach( (field) => {
        if(field.type != 'checkbox') {
          field.value = '';
        }
      });
    }

    return;
  }
}

document.addEventListener('change', (e) => {
  if( e.target.classList.contains('address-select') ) {
    window.e = e;
    let addressContainer = e.target.closest('.js-checkout-address');
    let disableInputs = typeof e.target.dataset.disableInputs !=="null" && e.target.dataset.disableInputs;
    let preFill = new preFillAddress(addressContainer, addressContainer.dataset.addressType, disableInputs);
    preFill.prefillAddress();
  }
});