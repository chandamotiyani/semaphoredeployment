import Vue from 'vue';
import Calendar from './components/Calendar';
import EventSelect from './components/EventSelect';
//import EventTotals from './components/EventTotals';
import { EVENT_ENDPOINT } from './config';

class EventDatePicker {
	constructor() {
		this.setupAjax();
		this.setupComponents();
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

		let _self = this;

		this.calendar = new Vue({
			extends: Calendar,
			el: '.js-date-picker',
			beforeMount: function() {
				this.events = JSON.parse(this.$el.dataset.schedule);
				this.eventId = this.$el.dataset.eventId;
				this.purchasableId = this.$el.dataset.purchasableId;
			},
			watch: {
				selectedDate: date => {
						this.eventSelect.selectedDate = date;
						this.eventSelect.eventId = this.calendar.eventId;
						this.eventSelect.busy = true;
					},
					date: date => {
						//_self.filterEventsToDay(date);
					},
			}
		});

		this.eventSelect = new Vue({
			extends: EventSelect,
			watch: {
				selectedDate: (date) => {
					if(isNaN(this.eventSelect.selectedDate.getDate())){
						//we've been sent an invalid date - set to today.
						this.eventSelect.selectedDate = new Date();
					}
					this.getEventsForDay(this.eventSelect.eventId, this.eventSelect.selectedDate);
					// set date for form value
				}
			},
			events: () => {

			}
		});
	}

	getEventsForDay(id, date) {

		let urlString = `?year=${date.getFullYear()}&month=${date.getMonth()+1}&eventId=${id}&day=${date.getDate()}`;

		let url = `${EVENT_ENDPOINT}${urlString}`;

		this.xhr.open("GET", url);
		this.xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		this.xhr.setRequestHeader(window.csrfTokenName, window.csrfTokenValue);
		this.xhr.setRequestHeader('Content-Type', 'application/json');

		this.xhr.send(); //send data
	}

	handleResponse(e) {
		if (this.xhr.readyState === 4 && this.xhr.status === 200) {
			this.eventSelect.events = JSON.parse(this.xhr.responseText);
			this.eventSelect.busy = false;

			try {
				if(typeof this.eventSelect.events !="undefined" && typeof this.eventSelect.events[0].s_id !="undefined") {


					let variantModalUpdated = new CustomEvent("update-add-to-cart-props", {
						"detail": {
							productid : this.eventSelect.events[0].id,
							purchasableid: this.eventSelect.events[0].s_id,
							schedule : this.eventSelect.events[0].startDateTime
						}
					});
					document.dispatchEvent(variantModalUpdated);
				}
			} catch {}
		}
	}
}

let datePicker = document.querySelector('.js-date-picker');

if (typeof (datePicker) != 'undefined' && datePicker != null) {
	new EventDatePicker(window.csrfTokenName, window.csrfTokenValue);
}

