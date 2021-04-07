const util = {
    addDynamicEventListener(selector, event, func) {
        document.addEventListener( event, (e) => {
            let type, name = selector;

            if ( selector.match(/^[.|#]/) ) {
                type = name[0];
                name = name.split(/^[.|#]/)[1];
            }

           let hasSelector = type ? e.target.closest( selector ) : e.target[0].tagName == name;

            if ( hasSelector ) {
                func();
            }
        });
    },

    viewType() {
        return document.querySelector('.primary-navigation__list').getClientRects().length ? 'desktop' : 'mobile';
    },

    formatMoney(amount, decimalCount = 2, decimal = ".", thousands = ",") {
        try {
          decimalCount = Math.abs(decimalCount);
          decimalCount = isNaN(decimalCount) ? 2 : decimalCount;
      
          const negativeSign = amount < 0 ? "-" : "";
      
          let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
          let j = (i.length > 3) ? i.length % 3 : 0;
      
          return negativeSign + (j ? i.substr(0, j) + thousands : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands) + (decimalCount ? decimal + Math.abs(amount - i).toFixed(decimalCount).slice(2) : "");
        } catch (e) {
          console.log(e)
        }
      }
}

export default util;