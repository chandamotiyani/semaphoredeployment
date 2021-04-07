
class Collapsible {
  constructor(collapsible) {

    this.collapsible = collapsible;
    this.collapsibleBody = document.createElement("div");
    collapsible.appendChild(this.collapsibleBody);
    this.collapsibleBody.classList.add('collapsible__body');

    this.collapsiblePanel = collapsible.querySelector('.collapsible__panel');
    this.collapsibleTrigger = collapsible.querySelector('.collapsible__trigger');
    this.breakpointHint = document.querySelectorAll('.hidden-mobile')[0];

    try {
      this.collapsibleTrigger.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopImmediatePropagation();
        this.toggle();
      }, false);

      this.collapsibleBody.addEventListener('click', (e) => {
        if(this.isDesktop()) {
          this.close();
        }
      }, false);
    } catch {}

  }

  closeAll() {
    let panels = document.querySelectorAll('.collapsible--active');
    if( typeof(panels) != 'undefined' && panels != null ) {
      panels.forEach((panel) => {
        panel.classList.remove('collapsible--active');
      });
    }
  }

  open() {
    if(this.isDesktop()) {
      this.closeAll();
    }

    this.collapsible.classList.add('collapsible--active');
  }

  close() {
    this.collapsible.classList.remove('collapsible--active');
  }

  toggle() {
    window.collapsible = this.collapsible;
    if(this.collapsible.classList.contains('collapsible--active')) {
      this.close();
    } else {
      this.open();
    }
  }

  isDesktop() {
    if(typeof this.breakpointHint !=="undefined") {
      return this.breakpointHint.getClientRects().length;
    }

    return true;
  }
}

let collapsible = document.querySelectorAll('.collapsible');
if( typeof(collapsible) != 'undefined' && collapsible != null ) {
collapsible.forEach(element => {
    new Collapsible(element);
  });
}
