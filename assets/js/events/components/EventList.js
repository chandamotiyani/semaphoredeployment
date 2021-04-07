require('../../utilities/date-format');
import forEach from 'lodash/forEach';
import clone from 'lodash/clone';
const listBaseClass = 'card-list-event-small';

function renderEventCards(createElement) {
	let listElements = [];

	forEach(this.events, event => {
		let item = renderEventCard(createElement, event);

		listElements.push(item);
	})

	let ulElement = createElement('ul', {
		class: listBaseClass
	}, listElements);

	let title = createElement('h3', {
		class: 'events__list-title'
	}, `${this.getEventsListTitle}` );

	return createElement('div', {
		class: 'events__list'
	}, [title, ulElement]);
}

function renderEventCard(createElement, event) {
	// Details section
	let baseClass = 'card-event-small';
	let title = createElement('h3', {
		class: baseClass + '__title'
	}, event.title);
	let times = createElement('div', {
		class: baseClass + '__dates'
	}, event.startFormatted +', '+event.startTime + ' - ' + event.endTime);
	let location = createElement('div', {
		class: baseClass + '__location'
	}, event.location);

	let price = Intl.NumberFormat('en-AU', { style: 'currency', currency: 'AUD' }).format(event.price);
	price = createElement('div', {
		class: baseClass + '__price'
	}, event.price > 0 ? `${ price } ${ event.ticketTypeText ? event.ticketTypeText : 'Per person' }` : 'Free');
	let details = createElement('div', {
		class: baseClass + '__details'
	}, [title, times, location, price])

	// Image section
	let image = createElement('div', {
		class: baseClass + '__image',
		style: `background-image: url(${event.image})`
	});
	let imageMask = createElement('div', {
		attrs: {
			class: baseClass + '__image-mask'
		}
	}, [image]);

	let item = createElement('a', {
		class: baseClass,
		attrs: {
			href: event.url,
		}
	}, [imageMask, details]);

	return createElement('li', {
		class: listBaseClass + '__item',
		style: event.hidden ? 'opacity: 0.25; display: none' : ''
	}, [item]);
}

function dayMonthYearToNumber(date) {
	return parseInt(`${date.getFullYear()}${date.getMonth()}${date.getDate()}`);
}

function isDateBetween(date, start, end) {
	if(!date) { return false }
    return dayMonthYearToNumber(date) <= dayMonthYearToNumber(end) && dayMonthYearToNumber(date) >= dayMonthYearToNumber(start);
}

const EventList = {
	name: 'event-list',
	props: ['events', 'selectedDate'],
	data: function() {
		return {
			monthSelected: false,
		}
	},
	watch: {
		selectedDate(date) {
			let hasSelectedEvents = false;
			this.showAllEvents = false;
			forEach(this.events, event => {
				let start = new Date(event.start);
				let end = new Date(event.end);
				event.hidden = false;
				if(date !== null && !isDateBetween(date, start, end)) {
					hasSelectedEvents = true;
					event.hidden = true;
				}
			});

			this.events = clone(this.events);
		}
	},
	computed: {
    getEventsListTitle: function () {
			let date = new Date(this.selectedDate);

			if(this.monthSelected) {
				return !isNaN(this.selectedDate) ? `Events in ${date.getMonthName()}` : ``;
			} else {
				return !isNaN(this.selectedDate) ? `Events on ${date.getDate()} ${date.getMonthName()}` : '';
			}
    }
  },
	render: renderEventCards,
	created() {
		this.events = [];
		this.showAllEvents = true;
	},
	el: '.js-events-list-items'
}

export default EventList;