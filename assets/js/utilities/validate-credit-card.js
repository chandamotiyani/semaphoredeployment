export default {

  invalidCard: function(cardNo) {

    if(cardNo == '') {
      return true;
    }
    var s = 0;
    var doubleDigit = false;
    for (var i = cardNo.length - 1; i >= 0; i--) {
        var digit = +cardNo[i];
        if (doubleDigit) {
            digit *= 2;
            if (digit > 9)
                digit -= 9;
        }
        s += digit;
        doubleDigit = !doubleDigit;
    }
    return !(s % 10 == 0);
  },

  invalidExp: function(exp) {

    const regex = RegExp(/(\d{2}\/\d{2})/);
    if(!regex.test(exp)) {
      return 'Invalid Expiry';
    }

    let expMonthYear = exp.split("/").map(function(expiry) {
      return expiry.trim();
    });

    if( typeof expMonthYear[0] == "undefined" || expMonthYear[0] == null  ) {
      return 'Invalid Month';
    }

    if( typeof expMonthYear[1] == "undefined" || expMonthYear[1] == null  ) {
      return 'Invalid Year';
    }

    // check month
    //let month = new Date().toISOString().substr(0, 19);
    if(expMonthYear[0] > 12 || expMonthYear[0] < 1) {
      return 'Invalid Month';
    }

    // check year
    let currentYear = new Date().toLocaleDateString('en', {year: '2-digit'});

    if(expMonthYear[1] < currentYear || ! /^\d{2}$/.test(expMonthYear[1])) {
      return 'Invalid Year';
    }

    return false;
  },

  invalidCVV: function(cvv) {
    cvv = toString(cvv);
    if(cvv.length < 3 ) {
      return 'Invalid CVV';
    }
  }

};