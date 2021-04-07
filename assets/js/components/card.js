import forEach from 'lodash/forEach';

let cards = [];

class Card {
  constructor(elem) {
    this.elem = elem;
    this.elem.addEventListener('click', e => { this.handleClick(e) });
    document.addEventListener('click', e => { this.handleDocumentClick(e) } );
    cards.push(this);
  }

  handleClick(e) {
    forEach(cards, (card) => {
      card === this ? card.select() : card.deselect();
    });
  }

  handleDocumentClick(e) {
    this.elem.classList.toggle('is-selected', this.elem.contains(e.target) && this.elem != e.target);
    this.elem.firstElementChild.classList.toggle('is-selected', this.elem.contains(e.target) && this.elem != e.target);
  }

  select() {
    this.elem.classList.add('is-selected');
    this.elem.firstElementChild.classList.add('is-selected');
  }

  deselect() {
    this.elem.classList.remove('is-selected');
    this.elem.firstElementChild.classList.remove('is-selected');
  }
}

class CardList {
  constructor(elem) {
    this.list = elem;
    document.addEventListener('click', e => { this.handleDocumentClick(e) } );
  }

  handleDocumentClick(e) {
    let isSelected  =
      this.list.contains(e.target)
      && !e.target.classList.contains('js-card-list')
      && !e.target.classList.contains('js-card-list__item');

    this.list.classList.toggle('is-selected', isSelected);
  }
}

// Cards

let cardElements = document.querySelectorAll('.js-article .js-card-list__item');

forEach(cardElements, (card) => {
  new Card(card);
});


// Card Lists

let listElements = document.querySelectorAll('.js-article .js-card-list');

forEach(listElements, (list) => {
  new CardList(list);
});