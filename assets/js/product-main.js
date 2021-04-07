/**
 * TODO: Turn this into something that can be used as a mixin to other sections that use common carousel functionallity ie. carousel-cards / product s
 */

const Flickity = require('flickity');
import Magnifier from 'magnifier';

export default class ProductMain {

  constructor(elem) {

    this.elem = elem;
    this.productSlider = elem.querySelector('.product__slider');

    if( this.productSlider.querySelectorAll('.product__image-slide').length < 2 ) {
      return;
    }
 
    this.flickityInstance = null; // flickity instance
    this.setCarousel(); // calls init when ready
    this.addMagnifier();
  }

  setCarousel() {
    new Flickity( this.productSlider, {
      // options
      cellAlign: 'left',
      freeScroll: false,
      contain: true,
      pageDots: false,
      prevNextButtons: false,
      imagesLoaded: true,

      on: {
        ready: () => {
          this.initSlider();
        }
      }
    });
  }

  addMagnifier() {
    let productImage = document.querySelector('.product__image');
    if( typeof(productImage) != 'undefined' && productImage != null ) {
      let magnifier =  new Magnifier('.product__image');
      magnifier.width(200);
      magnifier.height(200);
      magnifier.borderRadius('100%');
    }
  }

  initSlider() {
    this.flickityInstance = Flickity.data( this.productSlider );

    let prevButton = this.elem.querySelector('.product__carousel-prev');
    let nextButton = this.elem.querySelector('.product__carousel-next');


      if(prevButton.length) {
        prevButton.addEventListener('click', (e) => {
          e.preventDefault();
          this.flickityInstance.previous();
        }, false);

        nextButton.addEventListener('click', (e) => {
          e.preventDefault();
          this.flickityInstance.next();
        }, false);
      }

  }
}


let productMain = document.querySelectorAll('.product');
if( typeof(productMain) != 'undefined' && productMain != null ) {
  productMain.forEach( (product) => {
    new ProductMain(product);
  });
}
