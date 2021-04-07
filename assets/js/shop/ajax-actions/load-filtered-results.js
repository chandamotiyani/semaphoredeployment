/**
 * AJAX Update to Product listing on change in filter menu dropdown.
 */
import 'whatwg-fetch'; 
export default class loadFilteredResults {

  constructor(productListing, form) {

    let action = form.hasAttribute('action') ? form.getAttribute('action') : '';
    this.form = form;
    this.debounce = null;
    this.isLoading = false;
    this.controller = new AbortController();
    this.action = action;
    this.listing = productListing;
    this.debounceTime = 1000;
    this.queryString = '';

    this.listenForFilterChanges();


    if(window.location.search) {
      this.queryString = window.location.search.substr(window.location.search.indexOf('?') + 1);
      this.loadFilteredResults();
    }
  }

  /**
   * When a filter changes 'filters-updated' event is emmited.
   * (See ajax-filters.js)
   */
  listenForFilterChanges() {
    this.form.addEventListener('filters-updated', (e) => {
      this.queryString = e.queryString;
      this.loadFilteredResults();
     }, false);
  }

  /**
   * Generates a URL from the filtered form,
   * Does an AJAX request with that URL,
   * Loads in the partial from the AJAX request.
   */
  loadFilteredResults() {

    document.body.style.cursor='waiting';
    this.listing.classList.add('loading');
    let url = this.updateUrl();
    history.replaceState(document.location.host, document.title, url);

    clearTimeout(this.debounce);
    this.debounce = setTimeout( () => {
      this.loadData(url);
    }, this.debounceTime);
  }

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

    //try {
      const response = await fetch(`/products/products?${this.queryString}&productType=wine,premiumWine`, {
        method: 'GET',
        signal: this.controller.signal,
        headers: {
          "Content-Type": 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
      });

      let json = await response.json();

      document.body.style.cursor='pointer';
      //  productListing.innerHTML = html.querySelector('.js-product-listing').innerHTML;
      productListing.classList.remove('loading');

      this.filterProducts(json);

   // } catch(error) {

    //}

    this.isLoading = false;

  }

  filterProducts(productIds) {
    let products = document.querySelectorAll('[data-filter-item-id]');
    if( typeof(products) != 'undefined' && products != null ) {

      window.products = products;
      window.productIds = productIds;

      products.forEach((product) => {
        product.classList.add('hidden');
        let filterItemId = product.dataset.filterItemId * 1;

        if( productIds.includes(filterItemId) ) {
          product.classList.remove('hidden');
        }
      });

      /**
       * Sort v-dom elements
       */
      var refs = window.refs;

      productIds.forEach((productId) => {
        productId = productId*1;
        let itemKey = `item${productId}`;
        refs[itemKey].parentNode.appendChild(refs[itemKey]);
      });

    }
  }

}

let productListing = document.querySelector('.js-product-listing');
let form = document.querySelector('.js-product-filters');
if( typeof(productListing) != 'undefined' && productListing != null && form != null ) {
  new loadFilteredResults(productListing, form);
}
