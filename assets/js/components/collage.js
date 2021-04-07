import forEach from 'lodash/forEach';

class Collage {

  constructor(elem) {

    this.elem = elem;
		this.items = this.elem.querySelectorAll('*');

		this.elem.addEventListener('mouseenter', (e) => {
			this.handleMouseEnter();
		});
		
		this.elem.addEventListener('mouseleave', (e) => {
			this.handleMouseLeave();
		});

		this.handleMouseLeave();
  }

  handleMouseEnter() {
	forEach(this.items, (element, i) => {
		if(element.children[0] != undefined) {
			element.children[0].classList.remove('is-active');
		}
	});
  }

  handleMouseLeave() {
	forEach(this.items, (element, i) => {
		if(element.children[0] != undefined) {
			element.children[0].classList.toggle('is-active', i == 0);
		}
	});
  }
}

let lists = document.querySelectorAll('.js-card-list-two-column');

forEach(lists, (list) => {
	new Collage(list);
});
