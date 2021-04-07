import forEach from 'lodash/forEach';

class Paginate {

	constructor(loadMoreButton) {
		this.loadMoreButton = loadMoreButton;
		this.partialSelector = loadMoreButton.dataset.paginateSelector;
		this.url = this.loadMoreButton.href;
		this.container = document.querySelector(this.partialSelector);
		this.loadNextPage();
		this.loadMoreButtonSelector = `[data-paginate-selector="${loadMoreButton.dataset.paginateSelector}"]`;
	}

	async loadNextPage() {

		this.loadMoreButton.classList.add('is-busy');
		this.loadMoreButton.innerHTML = "Loading...";

		const updateHTML = await fetch(`${this.url}`, {
			method: 'GET',
		});

		let response = await updateHTML.text();

		let html = document.createElement('div');
		html.innerHTML = response;

		let loadMoreButton = html.querySelector(this.loadMoreButtonSelector);
		let partial = html.querySelector(this.partialSelector);


		if(loadMoreButton) {
			this.loadMoreButton.replaceWith(loadMoreButton);
		} else {
			this.loadMoreButton.remove();
		}

		try {
			for(var child of partial.children) {
				let newChild = child.cloneNode(true)
				this.container.appendChild(newChild);
			}
		} catch {}
		//history.replaceState(document.location.host, document.title, this.url);
		this.loadMoreButton.classList.remove('is-busy');


	}
}

document.addEventListener('click', (e) => {
  if(e.target.classList.contains('js-paginate')) {
		e.preventDefault();
		new Paginate(e.target);
  }
});
