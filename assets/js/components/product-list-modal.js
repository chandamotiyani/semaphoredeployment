class ProductListModal {

  constructor(modalId) {

    this.modalId = modalId;
    this.modal = document.querySelector(`[data-product-list-modal-id="${this.modalId}"]`)

    if(! this.modal) {
      return;
    }
    document.body.appendChild(this.modal);
    setTimeout( () => {
      this.modal.classList.add('product-list-modal--active');
    }, 100);
    document.body.classList.add('fixed');
    this.modal.addEventListener('click', (e) => {

      if( e.target.classList.contains('svg-close') ) {
        this.closeModal();
      }

      if( this.modal == e.target ) {
        this.closeModal();
      }

      return;
    });
  }

  closeModal() {
    let modalsAll = document.querySelector('.product-list-modal--active');
    if( typeof(modalsAll) != 'undefined' && modalsAll != null ) {
      modalsAll.classList.remove('product-list-modal--active');
      document.body.classList.remove('fixed');
    }
  }
}

let productListItem = document.querySelectorAll('[data-product-list-modal-id]');
if( typeof(productListItem) != 'undefined' && productListItem != null ) {
  document.body.addEventListener('click', (e) => {
    try {
      if( typeof e.target.dataset.forProductListModal !=="undefined" && e.target.dataset.forProductListModal != null  ) {
        e.preventDefault();
        new ProductListModal(e.target.dataset.forProductListModal);
      }
    } catch {}
  });
}
