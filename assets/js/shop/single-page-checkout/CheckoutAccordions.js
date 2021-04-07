import Jump from 'jump.js';
import { easeInOutQuad } from '../../utilities/easing';

export default class CheckoutAccordions {

  constructor(accordionClassSelector, scrollToView = true) {
    this.accordionElement = document.querySelector(accordionClassSelector);
    this.index = this.accordionElement.dataset.index;
    this.allAccordions = document.querySelectorAll('.accordion-tab');
    this.scrollToView = scrollToView;
  }

  closeOthers() {
    this.allAccordions.forEach(element => {
      element.classList.remove('accordion-tab--active');
    });
  }

  allowToggle() {
    this.allAccordions.forEach(element => {
      element.classList.remove('can-edit');

      if(element.dataset.index < this.index) {
        element.classList.add('can-edit');
      }
    });
  }

  open() {
    if(this.scrollToView) {

      this.allAccordions.forEach(element => {

        if(element.dataset.index <= this.index) {
          element.classList.add('accordion-tab--active');
          window.element = element;
        }
      });

      this.allowToggle();
      //this.closeOthers();
      Jump(this.accordionElement, {
        duration: 500,
        offset: -80,
        callback: undefined,
        easing: easeInOutQuad,
        a11y: false
      });
    } else {
      this.accordionElement.classList.add('accordion-tab--active');
    }
  }
}