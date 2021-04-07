Date.prototype.getMonthName = function() {
  var monthNames = [ "January", "February", "March", "April", "May", "June", 
                     "July", "August", "September", "October", "November", "December" ];
  return monthNames[this.getMonth()];
}