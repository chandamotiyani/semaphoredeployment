import Jump from 'jump.js';
import { easeInOutQuad } from '../utilities/easing';

const scrollButton = document.getElementById("hero__scroll");
const hero = document.querySelector(".hero");

if( scrollButton ) {
  scrollButton.addEventListener('click', (e) => {
    e.preventDefault();
    Jump(hero.nextElementSibling, {
      duration: 500,
      offset: 0,
      callback: undefined,
      easing: easeInOutQuad,
      a11y: false
    })
  }, false);
}
