import forEach from 'lodash/forEach';
import nth from 'lodash/nth'
import clone from 'lodash/clone';
// Importing Component and style
const VueSweetCalendar = require('vue-sweet-calendar').Calendar;
const brandPrimaryColour = '#c88242';
import DateTime from '../../utilities/DateTime';

// NOTE: As we don't have access to a Vue compiler in the gulpfile to compile single file .vue components,
// this extension of the vue-sweet-calendar component is in vanilla JS - which makes for some pretty unreadable
// render functions. If a Vue compiler is added to the gulpfile in the future, this would be worth refactoring into a more readable .vue file.


const EventCalendar = {
	extends: VueSweetCalendar,
	name: 'sweet-calendar',
	props: ['selectedDate'],
	el: '.js-events-calendar',
	data: function() {
		return {
			monthSelected: false,
		}
	},
	watch: {
		selectedDate(date) {
			let days = this.$el.querySelectorAll('.day');
			forEach(days, day => {
				try {
					let className = day.classList[1];
					let dayOfMonthNumber = parseInt(className.replace('day-', '').replace(',', ''));
					let isClickable = day.getAttribute('style') && day.getAttribute('style').length > 0 || day.classList.contains('today');
					day.classList.toggle('selected', date && dayOfMonthNumber == date.getDate() && isClickable);
				} catch {}
			});
		}
	},
	methods: {

		handleDayClick(target) {
			try {
				let day = target.querySelector('.day');
				let className = day.classList[1];
				let dayOfMonthNumber = parseInt(className.replace('day-', '').replace(',', ''));
				let newDate = new Date(this.date._date);
				newDate.setDate(dayOfMonthNumber);
				this.monthSelected = false;

				if(this.selectedDate === null || this.selectedDate.getTime() != newDate.getTime()) {
					this.selectedDate = newDate;
					// Vue.set(this.selectedDate, newDate); // Use the Vue instance to ensure that the watchers fire
				} else {
					// Vue.set(this.selectedDate, null);
					//this.selectedDate = null; // TODO: default to month

				}
			} catch {}

			// console.log(this.selectedDate.getTime(), newDate.getTime(), this.selectedDate.getTime() == newDate.getTime());

		},

		generateDayStyle(date) {
			let style = {}
			for (let event of this.events) {
				let dateIsFirst = date.isInRange(event.start, event.end, event.repeat) && !date.getPrevDay().isInRange(event.start, event.end, event.repeat);
				let dateIsLast = date.isInRange(event.start, event.end, event.repeat) && !date.getNextDay().isInRange(event.start, event.end, event.repeat);
				let dateIsInRange = date.isInRange(event.start, event.end, event.repeat);

				if (dateIsInRange) {
					let category = this.eventCategories.find(item => item.id === event.categoryId) || {}
					style['color'] = category.id ? brandPrimaryColour : null;

					// If a day is the first day of a multi-day event, round-off the left hand side.
					if (dateIsFirst && !dateIsLast) {
						style['border-top-left-radius'] = '50%';
						style['border-bottom-left-radius'] = '50%';
						style['border-top-right-radius'] = '0';
						style['border-bottom-right-radius'] = '0';
						style['border-top'] = `1px solid ${brandPrimaryColour}`;
						style['border-left'] = `1px solid ${brandPrimaryColour}`;
						style['border-bottom'] = `1px solid ${brandPrimaryColour}`;
					}

					// If a day is the last day of a multi-day event, round-off the right hand side.
					if (dateIsLast && !dateIsFirst) {
						style['border-top-right-radius'] = '50%';
						style['border-bottom-right-radius'] = '50%';
						style['border-top-left-radius'] = '0';
						style['border-bottom-left-radius'] = '0';
						style['border-top'] = `1px solid ${brandPrimaryColour}`;
						style['border-right'] = `1px solid ${brandPrimaryColour}`;
						style['border-bottom'] = `1px solid ${brandPrimaryColour}`;
					}

					// If a day falls on a single-day event OR it sits in between two bookends of a multi-day event, make it a circle.
					if(dateIsLast && dateIsFirst || !dateIsLast && !dateIsFirst) {
						style['border-radius'] = '50%';
					}

					// Single-day events are drawn as circles.
					if(dateIsLast && dateIsFirst) {
						style['border'] = `1px solid ${brandPrimaryColour}`;
					}

					if(typeof event.ticketsAvailable !=="undefined" && !event.ticketsAvailable) {
						style['opacity'] = '0.4';
						style['pointer-events'] = 'none';
					}
				}
			}
			return style
		},
		generateBeforeStyle(date) {
			let style = {}
			for (let event of this.events) {
				if (date.isInRange(event.start, event.end, event.repeat) && date.getPrevDay().isInRange(event.start, event.end, event.repeat)) {
					style['border-top'] = `1px solid ${brandPrimaryColour}`;
					style['border-bottom'] = `1px solid ${brandPrimaryColour}`;
				};
			}
			return style;
		},
		generateAfterStyle(date) {
			let style = {}
			for (let event of this.events) {
				if (date.isInRange(event.start, event.end, event.repeat) && date.getNextDay().isInRange(event.start, event.end, event.repeat)) {
					style['border-top'] = `1px solid ${brandPrimaryColour}`;
					style['border-bottom'] = `1px solid ${brandPrimaryColour}`;
				};
			}
			return style;
		},
		generateWeekdayNames(date) {

      var weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

			return weekdays;
		}
	},
	created() {
		// Get the requested year and month from the URL
		let splitPath = window.location.pathname.split('/');
		let month = parseInt(nth(splitPath, -1));
		let year = parseInt(nth(splitPath, -2));

		// Set the initial month of the calendar
		this.initialDate = new Date(year, month - 1);

		this.selectedDate = new Date(year, month - 1);

		// If initially loaded month is the current month...
		if(this.initialDate.getMonth() == new Date().getMonth()) {
			// Find the first day of the month that has events, and select it.
			this.initialDate = new Date();
		}

		this.monthSelected = false;
	},
	mounted() {
		this.$el.addEventListener('click', e => {
			if (e.target.classList.contains('day-container')) {
				this.handleDayClick(e.target);
			} else {
				this.date = new DateTime(this.selectedYear, this.selectedMonth - 1, 1)
				this.selectedDate = this.date;
				this.nextMonth();
				this.monthSelected = true;
			}
		});
	}
}

export default EventCalendar;