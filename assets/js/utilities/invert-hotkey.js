import forEach from 'lodash/forEach';

class InvertHotkey {

	constructor() {
		document.body.addEventListener('keydown', (e) => { this.handleKeyDown(e) });
	}

	handleKeyDown(e) {
		if(e.key == 'i') {
			document.body.classList.toggle('--invert');
		}
	}
}

new InvertHotkey();