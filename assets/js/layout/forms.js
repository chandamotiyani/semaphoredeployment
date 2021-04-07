import Choices from 'choices.js';
import debounce from 'lodash/debounce';


let select = document.querySelector('.choices');


if( typeof(select) != 'undefined' && select != null ) {
  new Choices('.choices', {
    itemSelectText: '',
    searchEnabled: false,
    shouldSort: false,
    placeholder: true,
    placeholderValue: 'Please Select'
  });
} 

document.addEventListener('click', (e) => {

  if(e.target.classList.contains('number-field__plus')) {
    let numberField = e.target.closest('.number-field');
    let numberInput = numberField.querySelector('input')

    numberInput.value = (numberInput.value*1)+1;
  }

  if(e.target.classList.contains('number-field__minus')) {
    let numberField = e.target.closest('.number-field');
    let numberInput = numberField.querySelector('input')

    let newValue = (numberInput.value*1)-1;

    if(newValue < 0) {
      return;
    }

    numberInput.value = newValue;
  }

});


document.addEventListener("keydown", debounce(function(e) {
  try {
    if(e.target.classList.contains("number-field__input")) {
      if(!Number.isInteger(e.target.value*1) || e.target.value == "") {
        return;
      }
     // e.target.closest('form').dispatchEvent(new Event('submit'));

     e.target.closest('form').querySelector('.js-hidden-submit').click();
    }
  } catch {}
}, 500));


document.addEventListener('change', (e) => {
  if(e.target.classList.contains('js-select-url')) {
    e.preventDefault();
    if(e.target.value !=="") {
      window.location = e.target.value;
    }
  }
});