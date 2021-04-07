
let date, reformatDate, targetName, targetContainer;

document.addEventListener('keyup', function(e) {
    if(! e.target.classList.contains('js-dob-mask')) {
        return;
    }

    targetContainer = e.target.parentElement;
    targetName = e.target.name;
    date = (e.target.value.split('/'));

    let day = date[0] ?? '';
    let month = date[1] ?? '';
    let year = date[2] ?? '';

    reformatDate = `${year}-${month}-${day}`;

    if(targetContainer !=null) {
        let dateField = targetContainer.querySelector('.js-dob-input');
        dateField.value = reformatDate;
    }
});
