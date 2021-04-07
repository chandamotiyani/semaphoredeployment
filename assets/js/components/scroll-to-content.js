import forEach from 'lodash/forEach';
import indexOf from 'lodash/indexOf';
import scrollMonitor from 'scrollmonitor';

class ScrollToContent {

	constructor(button, hero) {
		button.addEventListener('click', (event) => {
			event.stopPropagation();
			event.preventDefault();
			this.handleClick(event, hero);
		});
	}

	handleClick(event, hero) {
		let rect = hero.getBoundingClientRect();
		console.log('Rect:', rect.top + rect.height)

		try {
			window.scrollTo({ top: rect.top + rect.height, behavior: 'smooth' });
		} catch {}
	}
}

let buttons = document.querySelectorAll('.js-scroll-to-content');
let hero = document.querySelector('.js-hero');

console.log(hero);
console.log(buttons);

if (hero !== null && buttons !== null && buttons.length > 0) {
	forEach(buttons, (button) => {
		new ScrollToContent(button, hero);
	});	
}