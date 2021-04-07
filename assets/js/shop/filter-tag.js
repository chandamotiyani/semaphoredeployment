class FilterTag {
  constructor(tagId, tagName) {
    this.for = tagId;
    this.tagToClone = document.querySelector('.tag');
    this.tagWrapper = document.querySelector(".filters__filter-list");
    this.checkboxFor = document.getElementById(tagId);

    this.tag = this.tagToClone.cloneNode(true);
    this.tag.innerText = tagName;

    this.tagWrapper.appendChild(this.tag);
    this.tag.setAttribute("for", this.for);
    this.tag.style.display = "inline-flex";

    this.tag.addEventListener('click', () => {
      this.remove();
    });

  }

  remove() {
    this.checkboxFor.click();
    this.tag.remove();
  }
}

export { FilterTag };