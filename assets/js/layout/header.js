import Headroom from 'headroom.js';
import ScrollMagic from 'scrollmagic';
import Jump from 'jump.js';
/** Product Sticky Header */
let listingFilterHeader = document.querySelector('.list-section');
if(typeof listingFilterHeader && listingFilterHeader) {
  var controller = new ScrollMagic.Controller();

  new ScrollMagic.Scene({
    triggerElement: ".list-section",
    triggerHook: "onLeave",
  }).setClassToggle("body", "filters-stuck")
  .addTo(controller);
}


let productStickyHeader = document.querySelector('.product__row');
if(typeof productStickyHeader && productStickyHeader) {
  var controller = new ScrollMagic.Controller();
  new ScrollMagic.Scene({
    triggerElement: ".product__row",
    triggerHook: "onLeave",
  }).setClassToggle("body", "filters-stuck")
  .addTo(controller);
}

/* Stiky Header */
let header = document.querySelector(".primary-navigation");

if(typeof header && header) {

  const headRoom = new Headroom(header, {
    offset : 400,
    treshold : 5,
    onPin : function() {
      header.classList.add('has-pinned');
      header.classList.remove('headroom--pre-close');
      document.body.classList.add('header--pinned');

    },
    onTop : function() {
      header.classList.remove('has-pinned');
      header.classList.add('headroom--pre-close');
    },

    onUnpin : function() {
      document.body.classList.remove('header--pinned');
    }
  })

  headRoom.init();

  window.onscroll = getScrollPosition;
  var eTop = document.body.offsetTop; //get the offset top of the element
}


function getScrollPosition() {
  if( document.querySelector(".headroom--pre-close") != null ) {
    return;
  }

  if( window.pageYOffset > 150 ) {
  // document.querySelector('.headroom--not-top').classList.add('headroom--pre-close');
  }
}
