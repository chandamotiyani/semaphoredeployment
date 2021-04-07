let exceptionsList = document.querySelector('.exceptions-list');
if( typeof(exceptionsList) != 'undefined' && exceptionsList != null ) {

  function showModal() {

    if(window.location.hash == '#exceptions-list') {
      exceptionsList.classList.add('exceptions-list--active');
    } else {
      exceptionsList.classList.remove('exceptions-list--active');
    }
  }

  function hideModal() {
    if(window.location.hash == '#exceptions-list') {
      history.replaceState(null, null, ' ');
    }
  }
  let closeButton = document.querySelector('.exceptions-list__close-button');
  closeButton.addEventListener('click', (e) => {
    hideModal();
  });

  window.onhashchange = showModal;

  if(window.location.hash == '#exceptions-list') {
    showModal();
  }


  let last_known_scroll_position = 0;
  let ticking = false;

  function doSomething(scroll_pos) {
    if(window.location.hash == '#exceptions-list') {
      if(scroll_pos > 50) {
        hideModal();
        exceptionsList.classList.remove('exceptions-list--active');
      }
    }
  }

  window.addEventListener('scroll', function(e) {
    last_known_scroll_position = window.scrollY;

    if (!ticking) {
      window.requestAnimationFrame(function() {
        doSomething(last_known_scroll_position);
        ticking = false;
      });

      ticking = true;
    }
  });
}
