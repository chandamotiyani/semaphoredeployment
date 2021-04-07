import forEach from 'lodash/forEach';
import smoothscroll from 'smoothscroll-polyfill';

// kick off the polyfill!
smoothscroll.polyfill();
class ScrollIndicator {

	constructor(element, sections, previousButton, nextButton) {
		this.currentSectionId = 0;
		this.element = element;
		this.numeralElementContainer = this.element.querySelector('.js-scroll-indicator-items');
		this.watchers = [];
		this.numeralElements = [];
		this.sections = sections;
		this.currentSectionInViewIndex = 0;

		this.previousButton = previousButton;
		this.nextButton = nextButton;

		this.previousButton.addEventListener('click', (event) => {
			event.stopPropagation();
			event.preventDefault();
			this.scrollByDelta(-1);
		});

		this.nextButton.addEventListener('click', (event) => {
			event.stopPropagation();
			event.preventDefault();
			this.scrollByDelta(1)
		});

		// Set up children
		forEach(sections, (section) => this.setupSection(section));

		// Listen for the event.
		document.addEventListener('watchIndex', (e) => {
			this.currentSectionInViewIndex = e.detail.index;
			this.updateStepButtons(e.detail.index);
		}, false);
	}

	setupSection(section) {
		let numeralElement = document.createElement('li');
		let currentNumeralIndex = this.numeralElements.length;

		numeralElement.addEventListener('click', e => {
			this.scrollToIndex(currentNumeralIndex);
		});

		this.numeralElements.push(numeralElement);
		this.numeralElementContainer.appendChild(numeralElement);
	}


	updateStepButtons(index) {
		this.previousButton.classList.toggle('is-disabled', this.currentSectionInViewIndex <= 0);
		this.nextButton.classList.toggle('is-disabled', this.currentSectionInViewIndex >= this.sections.length - 1);

		for (var i = 0; i < this.numeralElements.length; i++) {
			this.numeralElements[i].classList.toggle('is-active', i == index);
		}
	}

	scrollToIndex(i) {
		try {
			window.scrollTo({
				top: this.sections[i].offsetTop,
				behavior: 'smooth'
			});
		} catch(e) {}
	}

	scrollByDelta(i) {
		this.scrollToIndex(this.currentSectionInViewIndex + i);
	}
}

let element = document.querySelector('.js-scroll-indicator');
let sections = document.querySelectorAll('.js-include-in-scroll-indicator');
let previous = document.querySelector('.js-section-step-previous');
let next = document.querySelector('.js-section-step-next');

if (element !== null && sections.length > 0) {
	new ScrollIndicator(element, sections, previous, next);
}