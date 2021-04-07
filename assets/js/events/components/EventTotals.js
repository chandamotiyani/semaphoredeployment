function renderTotals(createElement) {

	return createElement('span', {
		class: 'event__totals-total',
	}, this.total );

}

const EventTotals = {
	name: 'event-totals-total',
	props: ['total'],
	render: renderTotals,
	el: '.js-event-totals'
}

export default EventTotals;