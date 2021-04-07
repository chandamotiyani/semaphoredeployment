class Modal {

  constructor(modal) {

    if(! modal.id.length) {
      console.warn(`Can\'t initialize a modal on ${modal}. The modal needs an ID`);
      return;
    }

    this.modal = modal;
    this.modalID = modal.id;
    this.closeButton = this.modal.querySelector('.modal-close');

    // show the modal if the hash is in the url
    if( this.shouldShowModal() ) {
      this.showModal();
    }

    // close the modal
    if( typeof(this.closeButton) != 'undefined' && this.closeButton != null ) {
      this.closeButton.addEventListener('click', () => this.hideModal() );
    }

    // open the modal
    window.addEventListener('hashchange', () => this.showModal() );
  }

  showModal() {
    if(window.location.hash == '#'+this.modalID) {
      this.modal.classList.add('modal--active');

      let firstInput = this.modal.querySelector('input');
      if(firstInput !== null && typeof firstInput !=="undefined") {
        firstInput.focus();
      }
    } else {
      this.modal.classList.remove('modal--active');
    }
  }

  hideModal() {
    history.replaceState(null, null, ' ');

    // TODO: fix for ie11  
    try {
      window.dispatchEvent(new HashChangeEvent('hashchange'));
    } catch(e) {}

    this.modal.classList.remove('modal--active');

    let listModal = document.querySelector('.product-list-modal');

    if( typeof(listModal) != 'undefined' && listModal != null ) {
      listModal.classList.remove('product-list-modal--active');
      document.body.classList.remove('fixed');
    }
  }

  shouldShowModal() {
    return window.location.hash.length && window.location.hash == '#'+this.modalID;
  }
}

let modals = document.querySelectorAll('.modal');
if( typeof(modals) != 'undefined' && modals != null ) {
  modals.forEach( (modal) => {
    new Modal(modal);
  });
}
