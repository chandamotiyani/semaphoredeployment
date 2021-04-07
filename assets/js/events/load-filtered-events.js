/**
 * AJAX Update to Product listing on change in filter menu dropdown.
 */
import loadFilteredResults from '../shop/ajax-actions/load-filtered-results';
//import App from './app.vue';



class loadFilteredEvents extends loadFilteredResults {


  updateUrl() {
    let url = `${this.action}?${this.queryString}`;
    history.replaceState(document.location.host, document.title, url);
    return url;
  }

  /**
   * Do the AJAX request.
   */
  async loadData(url) {

    if(this.isLoading) {
      this.controller.abort();
    }

    this.isLoading = true;
    this.controller = new AbortController();

    const response = await fetch(`/events/?${this.queryString}`, {
      method: 'GET',
      signal: this.controller.signal,
      headers: {
        "Content-Type": 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    let res = await response.text();
    let html = document.createElement('div');
    html.innerHTML = res;

    this.isLoading = false;

    try {
        document.body.style.cursor='pointer';
        eventListing.innerHTML = html.querySelector('.js-event-listing .list-section').outerHTML;
        eventListing.classList.remove('loading');
        document.body.classList.add('filters-active');

        setTimeout(function() {
          document.body.classList.remove('filters-stuck');
        }, 500);
    } catch {}

  }

}

let eventListing = document.querySelector('.js-event-listing');
let form = document.querySelector('.js-product-filters');
if( typeof(eventListing) != 'undefined' && eventListing != null && form != null ) {
  new loadFilteredEvents(eventListing, form);
}
