
/* The calendar updates the url with a query string so linking to a hash is not reliable
 * to get around this run a jump after page loads if the url contains calendar to take
 * the screen to below the hero image. Other functions of the calendar are completed
 * with ajax calls and the default url to events is for the listing view so this should
 * only be run after a user clicks to change to calendar view.
 */

import Jump from 'jump.js';
import { easeInOutQuad } from '../utilities/easing';

const scrollButton = document.getElementById("hero__scroll");
const hero = document.querySelector(".hero");

document.addEventListener("DOMContentLoaded", function() {
	if (window.location.href.indexOf("/events/calendar") > -1) {
			Jump(hero.nextElementSibling, {
			duration: 0,
			offset: 0,
			callback: undefined,
			easing: easeInOutQuad,
			a11y: false
			})
	}
});