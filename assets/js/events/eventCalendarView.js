import Vue from 'vue';
import EventList from './components/EventList';
import Calendar from './components/Calendar';
import { EVENT_ENDPOINT, CALENDAR_URL } from './config';

class EventCalendarView {
	constructor() {
		window.yalCalendar = {};
		window.yalCalendar.state = {
			month: 	'',
			year: '',
		}

		this.setupAjax();
		this.setupComponents();
		this.watchFilterChanges();
	}

	setupAjax() {
		if(typeof window.csrfTokenName === 'undefined') {
			throw Error('CSRF token name is not defined.');
		}

		if(typeof window.csrfTokenValue === 'undefined') {
			throw Error('CSRF token value is not defined.');
		}

		this.xhr = new XMLHttpRequest();

		this.xhr.onreadystatechange = (e) => {
			this.handleResponse(e);
		}
	}

	setupComponents() {
		this.listTitle = document.querySelector('.js-events-list-title');

		let _self = this;

		this.eventList = new Vue({
			extends: EventList,
		});

		this.calendar = new Vue({
			extends: Calendar,
			watch: {
				selectedDate: date => {
					_self.filterEventsToDay(date);
				},
				date: date => {
					_self.dateQuery(date);
					_self.filterEventsToDay(date);
					_self.eventList.showAllEvents = true;
				},
				monthSelected: bool => {
					this.eventList.monthSelected = bool;
				}
			}
		});

		// this.addEventsToList(events);
		this.calendar.events = window.props.events;
		this.calendar.eventCategories = window.props.eventCategories;
	}

	watchFilterChanges() {
		let filterForm = document.querySelector('.js-product-filters');
    filterForm.addEventListener('filters-updated', (e) => {
			this.filterQuery(e.queryString);
		 }, false);
	}

	filterQuery(queryString) {
		// get date query
		const urlParams = new URLSearchParams(location.search);
		const params = Object.fromEntries(urlParams);

		let date = {
			month: params.month,
			year: params.year
		}

		this.getEvents(queryString, date);
	}

	dateQuery(dateObj) {
		const queryString = location.search;

		let date = {
			month: dateObj.getMonth(),
			year: dateObj.getFullYear()
		}

		this.getEvents(queryString, date);
	}

	getEvents(queryString, date) {
		const urlParams = new URLSearchParams(queryString);
		const params = Object.fromEntries(urlParams);

		params.month = date.month;
		params.year = date.year;

		let urlString = "?" + Object.keys(params).map(function(prop) {
			return [prop, params[prop]].map(encodeURIComponent).join("=");
		}).join("&");

		let eventGroupString = '/events';

		let endpointUrl = `${EVENT_ENDPOINT}${eventGroupString}${urlString}`;
		let pageUrl = `${CALENDAR_URL}${urlString}`;

		this.doAjaxRequest(endpointUrl, pageUrl);
	}


	doAjaxRequest(endpointUrl, pageUrl) {
		history.replaceState(document.location.host, document.title, pageUrl);

		this.xhr.open("GET", endpointUrl);
		this.xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		this.xhr.setRequestHeader(window.csrfTokenName, window.csrfTokenValue);
		this.xhr.setRequestHeader('Content-Type', 'application/json');

		document.querySelector('.js-events').classList.add('loading');

		this.xhr.send(); //send data
	}

	filterEventsToDay(date) {
		this.eventList.selectedDate = date;
	}

	handleResponse(e) {
		if (this.xhr.readyState === 4 && this.xhr.status === 200) {
			let events = JSON.parse(this.xhr.responseText);
			this.calendar.events = events;
			this.eventList.events = events;

			document.querySelector('.js-events').classList.remove('loading');
		}

		this.eventList.selectedDate = this.calendar.date._date;
	}
}

let datePicker = document.querySelector('.js-events-calendar');
if (typeof (datePicker) != 'undefined' && datePicker != null) {
		new EventCalendarView(window.csrfTokenName, window.csrfTokenValue);
}
