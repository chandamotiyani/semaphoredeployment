import { FilterTag } from './filter-tag';

class productFilters {
  constructor(form) {
    this.form = form;
    this.attachEvents();
    this.init();


    this.filterUpdatedEvent = new CustomEvent("filters-updated", {});
  }

  init() {
    this.form.querySelectorAll('input[type="checkbox"]:not(.check-all)').forEach( (checkbox) => {
      this.toggleFilterTag(checkbox);
    });

  }
  attachEvents() {
    this.form.addEventListener('submit', (e) => {
      e.preventDefault();
      this.dispatchEvent();
    });

    this.form.addEventListener('change', (e) => {

      if( e.target.type == 'checkbox' ) {
        e.preventDefault();

        if(e.target.classList.contains('check-all')) {

          e.target.parentElement.parentElement.parentElement.querySelectorAll('input[type="checkbox"]:not(.check-all)').forEach((checkbox) => {
            checkbox.checked = e.target.checked;
            this.toggleFilterTag(checkbox);
          })

        } else {
          this.toggleFilterTag(e.target);
        }

        this.dispatchEvent();
      }
    });


    // select box
    this.form.querySelectorAll('select').forEach( (select) => {
      select.addEventListener('change', (e) => {

        /** Hack to uncheck desktop select - (it's a form element with the same name displayed for desktop only) */
        let uncheckDesktopSortSelect = this.form.querySelector('input[name="sort"]');
        if(typeof uncheckDesktopSortSelect !=="undefined" && uncheckDesktopSortSelect !=null) {
          uncheckDesktopSortSelect.remove();
        }

        e.preventDefault();
        this.dispatchEvent();
      });
    });


    this.form.querySelectorAll('input[type="radio"]').forEach( (radio) => {

      radio.addEventListener('change', (e) => {

        /** Hack to uncheck desktop select - (it's a form element with the same name displayed for desktop only) */
        let uncheckMobileSortSelect = this.form.querySelector('select[name="sort"]');
        if(typeof uncheckMobileSortSelect !=="undefined" && uncheckMobileSortSelect !=null) {
          uncheckMobileSortSelect.remove();
        }

        e.preventDefault();
        this.dispatchEvent();
      });
    });

  }

  toggleFilterTag(checkbox) {
    if(checkbox.checked) {
      window.checkbox = checkbox;
      let text = checkbox.closest('label').querySelector('.form__checkout-label').innerHTML;
      new FilterTag(checkbox.id, text);
    } else {
      if(checkbox.id) {
        let label = document.querySelector(`label[for=${checkbox.id}]`);
        if(typeof label != null && label ) {
          label.remove();
        }
      }
    }
  }

  dispatchEvent() {
    this.filterUpdatedEvent.queryString = this.buildQueryString();

    this.form.dispatchEvent(this.filterUpdatedEvent);
  }


  buildQueryString() {
    let formData = Array.from(new FormData(this.form))
    var joinQueryParams = [];
    formData.forEach((formElement) => {
      if(formElement[0] !== 'sortmobile') {
        if(typeof(joinQueryParams[formElement[0]]) == "object" ) {
          joinQueryParams[formElement[0]].push(formElement[1])
        } else {
          joinQueryParams[formElement[0]] = [formElement[1]]
        }
      }
    });

    var url = "";
    for (var key in joinQueryParams) {
        if (url != "") {
          url += "&";
        }
        url += key + "=" + joinQueryParams[key].toString();
    }

    return url;
  }
}

let filters = document.querySelector('.js-product-filters');
if( typeof(filters) != 'undefined' && filters != null ) {
  let filter = new productFilters(filters);

  var evt = document.createEvent("HTMLEvents");
  evt.initEvent("submit", false, true);
  filter.dispatchEvent(evt);
}

/**
 * Apply filter button (just closes filter panel)
 */
let applyFilters = document.querySelectorAll('.js-apply-filters');

if(typeof applyFilters !=="undefined" && applyFilters !==null) {
  applyFilters.forEach((element) => {
    element.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopImmediatePropagation();

      let panels = document.querySelectorAll('.collapsible--active');
      if( typeof(panels) != 'undefined' && panels != null ) {
        panels.forEach((panel) => {
          panel.classList.remove('collapsible--active');
        });
      }

    }, false);
  });
}