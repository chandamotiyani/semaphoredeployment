import { Notification } from '../../utilities/notification';
import { easeInOutQuad } from '../../utilities/easing';
import Jump from 'jump.js';

export class CheckoutFormNotification extends Notification {
  notify() {
    this.appendTo = typeof this.notificationOptions.appendTo !=null ? this.notificationOptions.appendTo : '.checkout__continue';

    if(typeof appendTo == 'string') {
      this.appendTo = document.querySelector(`${this.notificationOptions.appendTo}`);
    }

    try {

      let oldMessage = this.notificationOptions.appendTo.querySelector('.js-msg');
      if(typeof oldMessage !="undefined" && oldMessage !=null){
        oldMessage.remove();
      }

      this.appendTo.appendChild(this.notification);

      Jump(this.notification, {
        duration: 500,
        offset: -100,
        callback: undefined,
        easing: easeInOutQuad,
        a11y: false
      });

    } catch {}
  }

  remove() {

  }
}