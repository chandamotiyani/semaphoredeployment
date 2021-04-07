export class CartUpdatedNotification {

  constructor(options) {

    let defaults = {
      type: 'success',
      message: 'Cart updated',
      container: ''
    }
    this.notificationOptions = Object.assign({}, defaults, options);
    this.openTimer = null;
    this.notify();
    this.remove();
  }

  notify() {
    window.location.hash = '';
    this.cartSummary = document.querySelector('.cart-summary');
    this.heading = this.cartSummary.querySelector('.cart-summary__col--right .cart-summary__heading');
    this.headingText = this.heading.innerHTML;

    this.cartSummary.classList.add('cart-summary--add-to-cart');
    this.cartSummary.classList.add('modal--active');
    this.heading.innerHTML = this.notificationOptions.message;


    this.cartSummary.addEventListener('click', () => {
      clearTimeout(this.openTimer);
    });

    window.addEventListener('hashchange', () => clearTimeout(this.openTimer) );
  }

  remove() {
    this.openTimer = setTimeout( () => {
      // close and clean up
      this.cartSummary.classList.remove('modal--active');
      this.heading.innerHTML = this.headingText;

      setTimeout( () => {
        this.cartSummary.classList.remove('cart-summary--add-to-cart');
       }, 800)
    }, 4000);
  }
}