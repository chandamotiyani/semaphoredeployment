let toggleTargets = document.querySelectorAll('.js-toggle-target');
if( typeof(toggleTargets) != 'undefined' && toggleTargets != null ) {

  toggleTargets.forEach( (toggleTarget) => {

    toggleTarget.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();

      let toggle = document.querySelector(`.${e.target.dataset.toggleTargetClass}`);
      if(typeof toggle !="undefined") {
        toggle.classList.toggle(`${e.target.dataset.toggleTargetClass}--active`);
        console.log(`No toggle class ${e.target.dataset.toggleTargetClass} found`);
      }

    }, false);
  });
}