let select = document.querySelector('.search__select');
if( typeof(select) != 'undefined' && select != null ) {
  select.addEventListener('change', event => {
    event.target.parentElement.querySelector('label').innerText = event.target.selectedOptions[0].innerText;

    document.getElementById('search-input').focus();
  });
}