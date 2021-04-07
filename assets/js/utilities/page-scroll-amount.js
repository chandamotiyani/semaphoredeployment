export function calculateScrollAmount () {
    let scrollContainer = document.querySelector('.js-article');
    let scrolled =  ((scrollContainer.clientHeight - window.pageYOffset) / 
    scrollContainer.clientHeight) * 100;

    return 100 - scrolled;
}