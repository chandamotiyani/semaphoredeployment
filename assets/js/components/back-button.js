class BackButton {

	constructor(button) {


			var referrer = document.referrer; 
			let a = document.createElement('a');
			a.href = referrer;

			if(a.hostname.includes('yalumba')) {
				button.href = referrer; // navigate to previous page
			}

	}
}

let buttons = document.querySelectorAll('.js-back-button');

if (buttons !== null && buttons.length > 0) {
	buttons.forEach(button => {
		new BackButton(button);
	});
}