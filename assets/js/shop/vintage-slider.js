let vintageSliders = document.querySelectorAll('.vintages-slider');
if( typeof(vintageSliders) != 'undefined' && vintageSliders != null ) {
  vintageSliders.forEach( (vintageSlider) => {

    let slide = vintageSlider.querySelector('.vintages-slider__slide--active');

    try {
      vintageSlider.addEventListener('mouseenter', (e) => {
        e.preventDefault();
        slide.classList.remove('vintages-slider__slide--active');
      }, false);

      vintageSlider.addEventListener('mouseleave', (e) => {
        e.preventDefault();
        slide.classList.add('vintages-slider__slide--active');
      }, false);
    } catch(e) {
      // no active slide.
    }
  });
}