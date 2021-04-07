/**
 * TODO: Turn this into something that can be used as a mixin to other sections that use common carousel functionallity ie. carousel-cards / product s
 */

const Flickity = require('flickity');


class MobileCarousel {


  constructor(elem) {

    this.elem = elem;
    this.carouselCardsElement = elem.querySelector('.carousel-container');
    this.flickityInstance = null; // flickity instance
    this.setCarousel(); // calls init when ready
  }

  setCarousel() {
    new Flickity( this.carouselCardsElement, {
      // options
      cellAlign: 'left',
      freeScroll: true,
      contain: true,
      pageDots: false,
      prevNextButtons: false,
      imagesLoaded: true,

      on: {
        ready: () => {
          this.init();
        }
      }
    });
  }

  init() {

    this.flickityInstance = Flickity.data( this.carouselCardsElement );
    this.flickityInstance.resize(); // reset flickity after load

    let progressBar = this.elem.querySelector('.progress__indicator');

    if(typeof progressBar =='null') {
      console.warn('Mobile Carousels need a progress bar');
      return;
    }
    this.flickityInstance.on( 'scroll', ( event, progress ) => {

      let width = this.flickityInstance.slider.scrollWidth;
      let viewportWidth = this.flickityInstance.size.width;
      let slideProgress = (progress * 100) + viewportWidth;
      let progressPercent = slideProgress / width;
      let slideIndicatorWidth = (viewportWidth / width) * 100;

      progressBar.style.width = `${slideIndicatorWidth}%`;
      progressBar.style.left = `${progressPercent}%`;

    });
  }
}


let carousels = document.querySelectorAll('.mobile-carousel');
if( typeof(carousels) != 'undefined' && carousels != null ) {
  carousels.forEach( (element) => {
    new MobileCarousel(element);
  });
}
