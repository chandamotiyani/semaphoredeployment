class AcceptCookies {

	constructor(button) {

		if(this.getCookie('gdpr')) {
			document.querySelector('.cookies-notice').classList.add('hidden');
		} else {
			document.querySelector('.cookies-notice').classList.remove('hidden');
		}

		button.addEventListener('click', (e) => {
			e.preventDefault();
			document.querySelector('.cookies-notice').classList.add('hidden');
			this.setCookie('gdpr', 1);
		});
	}

	getCookie(cookieName) {
		if( document.cookie ) {
				var cookie = document.cookie.split('; ').find(function(row) {
						return row.startsWith(cookieName)
				});

				if(cookie) {
						return cookie.split('=')[1];
				}
			}
	}

	setCookie(cookieName, cookieValue) {
			//document.cookie = cookieName+'='+cookieValue;
			var dt, expires;
			dt = new Date();
			dt.setTime(dt.getTime()+(30*24*60*60*1000));
			expires = "; expires="+dt.toGMTString();
			document.cookie = cookieName+"="+cookieValue+expires+"domain=." + 
			location.hostname.split('.').reverse()[1] + "." + 
			location.hostname.split('.').reverse()[0] + "; path=/";

	}
}

let buttons = document.querySelectorAll('.js-accept-cookies');

if (buttons !== null && buttons.length > 0) {
	buttons.forEach(button => {
		new AcceptCookies(button);
	});
}