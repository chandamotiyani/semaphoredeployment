import forEach from 'lodash/forEach';
import ScrollMagic from 'scrollmagic';
const Flickity = require('flickity');


/**
 * Apply a class after render of Flickity.
 * This allows us to set 100% height of an item
 */
Flickity.prototype._createResizeClass = function() {
  this.element.classList.add('flickity-resize');
};

Flickity.createMethods.push('_createResizeClass');

var resize = Flickity.prototype.resize;
Flickity.prototype.resize = function() {
  this.element.classList.remove('flickity-resize');
  resize.call( this );
  this.element.classList.add('flickity-resize');
};

class Carousel {

	constructor(elem) {
		this.items = elem.querySelector('.js-carousel-items');
		this.elem = elem;
		this.controller = null;
		this.scene = null;
		this.progressBar = null;
		this.oldViewType = null;
		this.hijack = null;
		this.breakpointHint = null;
		this.viewType = null;

		this.init();
	}

	init() {
		this.hijack = this.elem.classList.contains('--hijack-scroll');

		this.breakpointHint = document.querySelectorAll('.js-breakpoint-hint-desktop')[0];
		this.section = this.elem.parentElement.parentElement.parentElement;
		this.prevButton = this.section.querySelector('.js-carousel-prev');
		this.nextButton = this.section.querySelector('.js-carousel-next');

		this.flickity = new Flickity(this.items, {
			// options
			cellAlign: 'left',
			freeScroll: true,
			contain: true,
			pageDots: false,
			prevNextButtons: false,
			imagesLoaded: true,
			draggable: !this.hijack,
			imagesLoaded: true,
			on: {
				ready: () => {
					this.togglePagination();
				}
			}
		});

		window.addEventListener('resize', () => { // check screen size and show either carousel or slider depending on mobile / desktop
			this.updateViewType();
			this.togglePagination();
		});

		this.initPrevNextButtons();
		this.initProgressBar();

		// This triggers a redraw to ensure that other controls like the progressbar also get redrawn.
		this.flickity.resize(); // reset flickity after load

		this.updateViewType();

		let lastIndex = 0;
		this.flickity.on('change', ( index ) =>  {
			this.nextButton.classList.remove('disabled');
			this.prevButton.classList.remove('disabled');

			if(index + 1 >= this.flickity.cells.length) {
				this.nextButton.classList.add('disabled');
			}

			if(index == 0 ) {
				this.prevButton.classList.add('disabled');
			}

			if(lastIndex < index) {
				try {
					let lastCell = this.flickity.getLastCell().element;
					let cellPos = (lastCell.getBoundingClientRect().x - lastCell.getBoundingClientRect().width);
					if(cellPos < this.flickity.size.width) {
						this.nextButton.classList.add('disabled');
					}
				} catch {}
			}

			lastIndex = index;
		});

		window.addEventListener('load', (event) => {
			this.flickity.resize(); 
		});

	}

	initPrevNextButtons() {
		try {
			this.prevButton.addEventListener('click', (e) => {
				e.preventDefault();
				this.flickity.previous();
			}, false);

			let lastCell = this.flickity.getLastCell();
			this.nextButton.addEventListener('click', (e) => {
				e.preventDefault();
				this.flickity.next();

			}, false);
		} catch {}
	}

	togglePagination() {

			let carouselControls = this.section.querySelector('.carousel-controls');

			try {
				if(typeof carouselControls !="undefined" && typeof carouselControls !=null) {
					carouselControls.classList.remove('hidden');
				}

				let carouselButtons = this.section.querySelector('.carousel-controls__chevron-buttons');

				if(typeof carouselButtons !="undefined" && typeof carouselButtons !=null) {
					carouselButtons.classList.remove('hidden');
				}
			} catch {}

			let scrollWidth = this.section.querySelector('.flickity-slider').scrollWidth;
			let clientWidth = this.section.querySelector('.flickity-slider').clientWidth;

			if(clientWidth + 50 >= scrollWidth) {
				if (document.getElementsByClassName('carousel-controls__buttons').length > 0) {
					let buttons = this.section.querySelector('.carousel-controls__chevron-buttons');

					if(typeof buttons !='undefined' && buttons) {
						buttons.classList.add('hidden');
					}
				} else {
					let controls = this.section.querySelector('.carousel-controls__chevron-buttons');

					if(typeof controls !='undefined' && controls) {
						controls.classList.add('hidden');
					}
				}
			}

	}

	initProgressBar() {
		this.progressBar = this.section.querySelector('.js-scrollbar-handle');

		// if (!this.breakpointHint.getClientRects().length) return

		this.flickity.on('scroll', (event, progress) => {
			this.updateScrollBar(event, progress)
		});
	}

	updateScrollBar(event, progress) {
		if (this.progressBar != undefined) {
			let width = this.flickity.slider.scrollWidth;
			let viewportWidth = this.flickity.size.width;
			let slideProgress = (progress * 100) + viewportWidth;

			let progressPercent = slideProgress / width;
			let slideIndicatorWidth = (viewportWidth / width) * 100;
			this.progressBar.style.width = `${slideIndicatorWidth}%`;
			this.progressBar.style.left = `${progressPercent}%`;
		}
	}

	/**
	 * Use ScrollMagic to control Flickity slider position
	 */
	initScrollMagic() {
		if (this.flickity == undefined || !this.hijack) {
			return;
		}

		this.controller = new ScrollMagic.Controller({
			refreshInterval: 0,
		});

		this.scene = scrollMagic.setPin(".js-article").addTo(this.controller);

		this.updateScrollMagicOffset();

		/**
		 * Move Slider Horizontally
		 */
		this.scene.on("progress", (event, progress) => {
			this.updateSlider(event, progress);
			this.updateScrollBar(event, progress);
		});
	}

	updateSlider(event) {
		let scrollLength = this.flickity.slider.scrollWidth - this.flickity.viewport.offsetWidth;
		let progress = event.progress * 100;
		let scrollValue = (scrollLength * progress) / 100;
		this.flickity.slider.style.transform = `translate(-${scrollValue}px, 0)`;

		this.updateScrollBar(event, scrollValue);
	}

	/**
	 * Update scrollmagic offset to sit in the middle of the slide
	 */
	updateScrollMagicOffset() {
		let manualOffset = 40;
		let offset = (window.innerHeight - this.elem.offsetHeight) / 2;

		if (this.scene != null) {
			this.scene.offset(this.elem.offsetTop - offset + manualOffset);
		}
	}


	/**
	 * If mobile, kill scroll magic and just use normal carousel.
	 * If desktop, disable carousel dragable option and re-init scroll magic.
	 */
	updateViewType() {
		let newViewType = this.breakpointHint.getClientRects().length ? 'desktop' : 'mobile'; // check the CURRENT view type - mobile or desktop (checks if progress bar is visible

		if (newViewType != this.oldViewType) {
			this.oldViewType = this.viewType;

			this.flickity.options.draggable = (this.viewType == 'mobile') || !this.hijack; // if the current view is mobile, we want to enable drag on flickity
			this.flickity.updateDraggable();

			newViewType == 'mobile' ? this.destroyScrollMagic() : this.initScrollMagic();
		}

		this.viewType = newViewType;

		this.updateScrollMagicOffset();
	}

	destroyScrollMagic() {
		if (this.controller != null) {
			this.controller.destroy(true);
			this.controller = null;
		}

		if (this.scene != null) {
			this.scene.destroy(true);
			this.scene = null;
		}

		if (this.controller != null) {
			this.controller = null;
		}

		this.flickity.slider.style.transform = `translate(0)`;
	}

}

let carousels = document.querySelectorAll('.js-carousel');

let scrollMagic;

if(carousels.length > 0) {
	scrollMagic = new ScrollMagic.Scene({
		triggerElement: ".js-carousel",
		triggerHook: "onLeave",
		duration: "100%",
	});

	forEach(carousels, (element) => {
		new Carousel(element);
	});
}
