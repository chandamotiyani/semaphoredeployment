
class TextBreak {
  
    constructor( selector ) {
        this.selector = selector || '.text-break';
        this.elems = [];
        this.splitLength = 22;
        this.resize = false;
        this.inProgress = false;
        this.init();
    }
    
    init() {
        this.setElements();
        this.breakElements();
        this.updateFrame();

        window.addEventListener('resize', this.windowResize.bind(this));
    }
    
    /**
     * 
     * Sets all the elements based off selector
     */
    setElements() {
        let elems = document.querySelectorAll( this.selector );
        elems.forEach( element => {
            if (!element.hasAttribute('data-original-text')) {
                element.setAttribute('data-original-text', element.innerHTML);  
            }
        });
        this.elems = elems;
    }
    
    /**
     * 
     * Function to process all elements and break them if required
     */
    breakElements() {
        this.inProgress = true;

        this.elems.forEach( element => {
            element.innerHTML = element.getAttribute('data-original-text');
            let newText = this.breakText( element.getAttribute('data-original-text') );
            
            // Create hidden content to see if the new content will actually fit the container
            let sizeDiv = document.createElement('div');
            sizeDiv.innerHTML = newText;
            sizeDiv.style.position = 'absolute';
            sizeDiv.style.visibility = 'hidden';
            sizeDiv.style.whiteSpace = 'nowrap';
            
            element.prepend( sizeDiv );
            
            if ( this.getWidth( sizeDiv ) <= this.getWidth( element ) ) {
                element.innerHTML = newText;
            } else {
                element.innerHTML = element.getAttribute('data-original-text');
            }
        });

        this.inProgress = false;
    }
    
    /**
     * 
     * Breaks the text based on the split length
     */
    breakText( content ) {
    
        if ( content.length >= this.splitLength + 10 ) {
        let split = content.length / Math.round( content.length / this.splitLength );
        let before = content.lastIndexOf(' ', this.splitLength);
        let after = content.indexOf(' ', this.splitLength + 1);

        if (this.splitLength - before < after - this.splitLength) {
            split = before;
        } else {
            split = after;
        }
        
        let start = content.substr( 0, split );
        let end = content.substr( split + 1 );
        
        if ( end.length > this.splitLength ) end = this.breakText( end );
            return start + "<br />" + end;
        } else {
            return content;
        }
    }
    
    /**
     * 
     * Gets the width of an element without padding
     */
    getWidth( element ) {
        let computedStyle = getComputedStyle(element);
        let elementWidth = element.clientWidth; 
        elementWidth -= parseFloat(computedStyle.paddingLeft) + parseFloat(computedStyle.paddingRight);
        return elementWidth;
    }

    windowResize() {
        clearTimeout( this.resize );
        this.resize = setTimeout( () => {
            this.resize = false;
            this.breakElements();
        }, 50);
    }

    updateFrame() {
        if ( this.resize ) {
            if ( !this.inProgress ) this.breakElements();
        }
        requestAnimationFrame( this.updateFrame.bind(this) );
    }
  
}

new TextBreak();