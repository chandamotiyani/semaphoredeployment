class TabbedPanels {

  constructor(elem) {
    this.elem = elem;
    this.panels = elem.querySelectorAll('.js-tabbed-panels-tab');

    this.init();
  }

  init() {
    document.querySelectorAll('[data-panel-index]').forEach( (tab) => {
      tab.addEventListener('click', (e) => {
        e.preventDefault();

        this.removeClasses('panel-index--active');

        e.target.classList.add('panel-index--active');
        this.switchPanel(e.target.dataset.panelIndex);
      }, false);
    });

    this.switchPanel(0);
  }

  switchPanel( panelIndex ) {

    try {
      let scrollTo   = this.panels[panelIndex].offsetLeft - this.elem.offsetLeft;

     // this.elem.style.height = `${this.panels[panelIndex].clientHeight}px`;

      window.scrollTo(0, this.elem.offsetTop - 30);
      this.elem.scrollTo({
        top: 0,
        left: scrollTo,
        behavior: 'smooth'
      });
    } catch(error) {
      //console.log(error);
    }
  }

  removeClasses(className) {
    document.querySelectorAll(`.${className}`).forEach( (e) => {
      e.classList.remove(className);
    });
  }
}

let tabbledPanels = document.querySelectorAll('.js-tabbed-panels');
if( typeof(tabbledPanels) != 'undefined' && tabbledPanels != null ) {
  tabbledPanels.forEach( (tabbledPanel) => {
    window.tab = new TabbedPanels(tabbledPanel);
  });
}