import forEach from 'lodash/forEach';
import indexOf from 'lodash/indexOf';
import clone from 'lodash/clone';
import scrollMonitor from 'scrollmonitor';

class WatchViewport {
	constructor(sections) {
		this.watchers = [];

		this.event = document.createEvent('CustomEvent');


		forEach(sections, element => {
			this.setupSection(element);
		});

		scrollMonitor.update();

		this.showInitialSection();
	}

	showInitialSection() {
		let distancesFromViewportCentre = [];
		
		// Figure out which section's rect is covering the largest port of the viewport on initial load, and show it first.

		forEach(this.watchers, (watcher, i) => {
			let sectionMidYPoint = watcher.top + watcher.height / 2;
			distancesFromViewportCentre.push(sectionMidYPoint);
		});

		let viewportMidYPoint = scrollMonitor.viewportTop + scrollMonitor.viewportHeight / 2;
		let closest = clone(distancesFromViewportCentre).sort( (a, b) => Math.abs(viewportMidYPoint - a) - Math.abs(viewportMidYPoint - b) )[0];

		let startingWatcherIndex = indexOf(distancesFromViewportCentre, closest);
		let startingWatcher = this.watchers[startingWatcherIndex];

		this.showSection(startingWatcher);
	}

	setupSection(element) {
		let watcher = scrollMonitor.create(element, { top: -200, bottom: -200 });
		this.watchers.push(watcher);

		watcher.enterViewport((event, watchedItem) => { this.handleEnterViewport(event, watchedItem) });
	}

	handleEnterViewport(event, watchedItem) {
		this.showSection(watchedItem);
	}

	showSection(itemToShow) {
		forEach(this.watchers, (watcher, i) => {
			let visible = itemToShow === watcher;
			watcher.watchItem.classList.toggle('is-out', !visible);

			if(visible) {
				var event = new CustomEvent('watchIndex', { detail: { 'index': i }});
				document.dispatchEvent(event);
			}
		});
	}
}

let sections = document.querySelectorAll('.js-watch-viewport');

if (sections !== null && sections.length > 0) {
	new WatchViewport(sections);

	sections.forEach(function(section){
		let wrapContents = section.querySelector('.card-text-with-image__details');
		let toWrap = wrapContents.childNodes;

		let wrapper = document.createElement('div');
		wrapContents.appendChild(wrapper);

		for (var i = 0; i < toWrap.length; i++) {
			wrapper.appendChild(toWrap[i])
	}

	});
}