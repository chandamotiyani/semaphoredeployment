import util from './../utilities/util';

class MenuNavigation {

  constructor(elem) {

    this.elem = elem;
    this.subMenuTrigger = elem.querySelectorAll('.navigation-hover');
    this.initEvents();
    this.height = elem.offsetHeight;


    let setActive = this.elem.querySelector('.footer-navigation__link--active');

    if(typeof setActive !=="undefined" && setActive !==null) {
      //  setActive.click();
      let e = {
        target: setActive
      }
      this.activateSubMenu(e);
    }
  }

  initEvents() {

    /**
     * Activate sub menu on link interaction
     */
    this.subMenuTrigger.forEach( (el) => {

      el.addEventListener('mouseenter', (e) => {
        if(util.viewType() == 'mobile') {
          return;
        }

        e.preventDefault();
        this.activateSubMenu(e);
      }, false);

      el.addEventListener('click', (e) => {
        if(util.viewType() == 'desktop') {
          return;
        }
  
        e.preventDefault();
        this.activateSubMenu(e);
      }, false);
    });


    /**
     * Reset sub menu on mouse leave
     */
    this.elem.addEventListener('mouseleave', (e) => {
      if(util.viewType() == 'mobile') {
        return;
      }

      e.preventDefault();
      this.resetMenu(e);
    });


    /**
     * Back Button
     */
    this.elem.querySelectorAll(".footer-navigation__item--title").forEach( (el) => {
      el.addEventListener('click', (e) => {
        e.preventDefault();

        this.closeAllSubMenus();
      });
    });
  }

  closeAllSubMenus() {
    this.elem.style.height = `auto`;
    this.elem.querySelectorAll(".navigation-hover--active").forEach( (el) => {
      el.classList.remove('navigation-hover--active');
    });
  }

  resetMenu(e) {
    this.closeAllSubMenus();
    e.target.classList.add('footer-navigation--default');
  }

  activateSubMenu(e) {
    this.closeAllSubMenus();
    e.target.parentElement.classList.add('navigation-hover--active'); // activate
    this.elem.classList.remove('footer-navigation--default'); // remove the placeholder menu

    let childMenu = e.target.parentElement.querySelector('.footer-navigation__list--right');
    if( typeof childMenu !="undefined" && childMenu !=null) {
      childMenu.style.position = 'relative';
      if(this.height < childMenu.offsetHeight+30) {
        this.elem.style.height = `${childMenu.offsetHeight+30}px`;
      }

      childMenu.style.position = 'absolute';
    }
  }
}

let navigationMenus = document.querySelectorAll('.footer-navigation');
if( typeof(navigationMenus) != 'undefined' && navigationMenus != null ) {
  navigationMenus.forEach( (element) => {
    new MenuNavigation(element);
  });
}


/**
 * Open and close the popover menu
 */
const menuTrigger = document.getElementById("data-menu-open");
const menuTriggerClose = document.getElementById("data-menu-close");
const documentElement = document.querySelector("html");

if(typeof menuTrigger && menuTrigger) {
  menuTrigger.addEventListener('click', (e) => {
    e.preventDefault();
    documentElement.classList.add('popout-menu--visible');
  }, false);

  menuTriggerClose.addEventListener('click', (e) => {
    e.preventDefault();
    documentElement.classList.remove('popout-menu--visible');
  }, false);

}
