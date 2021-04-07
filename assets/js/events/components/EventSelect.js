require('../../utilities/date-format');
import forEach from 'lodash/forEach';

function renderSelect(createElement) {
	let options = [];
	let eventId = this.eventId;
	
	forEach(this.events, event => {
		options.push(createElement('option', {
			attrs: {
				value: event.s_id,
			}
		}, `${event.startTime} - ${event.endTime}` ));
	});


	return createElement('select', {
		class: 'select',
		attrs: {
			disabled: this.busy ? true : false,
			name: 'purchasableId',
		},
		on: {
			change(e) {
				let purchacableID = event.target.value;
				let id = eventId;
				//document.querySelector('[name="purchasableId"]').value = this.calendar.eventId;
				let calendarUpdated = new CustomEvent("update-add-to-cart-props", {
					"detail": {
						purchasableid: purchacableID,
						productid: eventId
					}
				});
				document.dispatchEvent(calendarUpdated);

			}
		}
	}, options );

}

const EventSelect = {
	name: 'event-list',
	props: ['selectedDate', 'events', 'busy', 'purchasableId'],
	render: renderSelect,
	el: '.js-event-time-select',

	methods:{
		signalChange: function(evt){
			this.$emit("change", evt);
		}
	}
}

export default EventSelect;