import forEach from 'lodash/forEach';

class DropDown {

	constructor(element) {
		this.attachEvents();
	}

	attachEvents() {
		document.body.addEventListener('click', (e) => {
			if( e.target.classList.contains('dropdown__title') ) {
				this.toggle(e.target);
			} else {
				this.close();
			}
		});
	}

	toggle(element) {
		element.classList.toggle('dropdown--active');
	}

	close() {
		let dropdowns = document.querySelectorAll('.dropdown')

		forEach(dropdowns, (dropdowns) => {
			dropdowns.querySelector('.dropdown__title').classList.remove('dropdown--active');
		});
	}
}

let dropdowns = document.querySelector('.dropdown');

if (typeof (dropdowns) != 'undefined' && dropdowns != null) {
	new DropDown(dropdowns);
}