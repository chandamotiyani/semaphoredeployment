export class Notification {

  constructor(options) {

    let defaults = {
      type: 'success',
      message: 'Cart updated',
      appendTo: '',
      notificationClass: 'notification',
    }

    this.notificationOptions = Object.assign({}, defaults, options);
    this.createNotificationElement();
    this.notify();
    this.remove();
  }

  createNotificationElement() {
    this.notification = document.createElement('div');
    this.notification.classList.add(this.notificationOptions.notificationClass);
    this.notification.innerHTML = this.notificationOptions.message;
    this.notification.classList.add(`${this.notificationOptions.notificationClass}--${this.notificationOptions.type}`);
  }

  notify() {
    let nav = document.querySelector('.primary-navigation');
    nav.classList.add('headroom--pinned'); // open nav before showing notification as nav contains cart qty etc.
    nav.appendChild(this.notification);
  }

  remove() {
    setTimeout( () => {
      this.notification.classList.add('notification--hide');
      setTimeout( () => {
       this.notification.parentNode.removeChild(this.notification);
      }, 800)
    }, 12000);
  }
}
