import 'whatwg-fetch'; 

export default class AjaxReloadPartial {

  constructor(selector, callback, loadingClass=false, changePartial) {
    this.selector = selector;
    this.getPartial();
    this.callback = callback;
    this.changePartial = changePartial;
    this.loadingClass = loadingClass;

    if(this.loadingClass) {
      try {
        document.querySelector(this.loadingClass).classList.add('loading');
      } catch {}
    }
  }

  async getPartial() {

    const updateHTML = await fetch(window.location, {
      method: 'GET',
    });

    let response = await updateHTML.text();
    let html = document.createElement('div');
    html.innerHTML = response;

    this.updatePartial(this.selector, html);
  }

  updatePartial(partialSelector, html) {

    let updatedPartial = html.querySelectorAll(partialSelector), i;

    if(this.changePartial && {}.toString.call(this.changePartial) === '[object Function]') {
      this.changePartial(updatedPartial[0]);
    }

    // loop through all partialSelector incase more than one on the page like with wishlist/cart
    for (i = 0; i < updatedPartial.length; ++i) {
      if( typeof(updatedPartial[i]) != 'undefined' && updatedPartial[i] != null ) {
        let oldPartial = document.querySelectorAll(partialSelector);

        if( typeof(oldPartial[i]) != 'undefined' && oldPartial[i] != null ) {
          oldPartial[i].innerHTML = updatedPartial[i].innerHTML;
        }
      }
    };

    if(this.callback && {}.toString.call(this.callback) === '[object Function]') {
      this.callback(true);
    }


    if(this.loadingClass) {
      try {
        document.querySelector(this.loadingClass).classList.remove('loading');
      } catch {}
    }

  }
}