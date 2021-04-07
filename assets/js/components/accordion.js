import forEach from 'lodash/forEach';
import Jump from 'jump.js';
import { easeInOutQuad } from '../utilities/easing';

class Accordion {

	constructor(elem) {
		this.element = elem;

		// Set up children
		forEach(elem.children, (elem) => this.setupChild(elem));
	}

	setupChild(elem) {
		// Add click listener
		elem.querySelector('.card-accordion__title').addEventListener('click', (elem) => this.onItemClick(elem), true);
	}

	onItemClick(event) {
		event.stopPropagation();
		event.preventDefault();

		let accordionElement = event.currentTarget.parentNode.parentNode;
		// Apply .is-open to the selected child, and remove it from all siblings
		forEach(this.element.children, (elem) => {
			if(elem !== accordionElement) {
				elem.classList.remove('is-open');
			}
		});
		accordionElement.classList.toggle('is-open');

		setTimeout(function() {
			Jump(accordionElement, {
				duration: 200,
				offset: -80,
				callback: undefined,
				easing: easeInOutQuad,
				a11y: false
			});
		}, 500);
	}

}

let list = document.querySelectorAll('.js-card-list-accordion');

if (typeof (list) != 'undefined' && list != null) {
	forEach(list, (element) => {
		new Accordion(element);
	});
}