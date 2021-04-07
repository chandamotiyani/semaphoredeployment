import forEach from 'lodash/forEach';
import Player from '@vimeo/player';

class VimeoPlayer {

	constructor(element) {
		let isPlaying = false;

		if (element.dataset.id == undefined) {
			return;
		}

		console.log('Background:', typeof element.dataset.background);

		let options = (typeof element.dataset.background !== 'undefined') ? {
				id: element.dataset.id,
				background: true
				// ^ background: true will automatically disable all controls, set loop to true, autoplay to true and mute audio.
				// https://vimeo.zendesk.com/hc/en-us/articles/360001494447-Using-Player-Parameters
			} :
			{
				id: element.dataset.id,
				byline: "false",
				color: "FFFFFF",
				controls: "false",
				fun: "false",
				playsinline: "false",
				dnt: true // Tell Vimeo not to track views
			};

		let player = new Player(element, options);


		player.on('pause', event => {
			isPlaying = false;
			element.classList.remove('is-playing');
		});

		player.on('play', event => {
			isPlaying = true;
			element.classList.add('is-playing');
			element.classList.add('has-played');
		});

		element.addEventListener('click', event => {
			isPlaying ? player.pause() : player.play()
		})
	}
}

let players = document.querySelectorAll('.js-vimeo');

if (typeof (players) != 'undefined' && players != null) {
	forEach(players, (element) => {
		new VimeoPlayer(element);
	});
}