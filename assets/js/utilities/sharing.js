import util from './util';

class SocialSharing {

    constructor() {
        this.windowWidth = 550;
        this.windowHeight = 450;

        this.shareLinks = {
            facebook: "https://www.facebook.com/sharer/sharer.php?u={url}",
            twitter: "http://twitter.com/share?url={url}&text={text}"
        };

        this.addListeners();
    }

    addListeners() {
        util.addDynamicEventListener( '.share-facebook', 'click', this.facebook.bind(this) );
        util.addDynamicEventListener( '.share-twitter', 'click', this.twitter.bind(this) );
    }

    openWindow( linkName, text ) {
        let left = (window.screen.availLeft + (window.screen.availWidth / 2)) - (this.windowWidth / 2);
        let top = (window.screen.availTop + (window.screen.availHeight / 2)) - (this.windowHeight / 2);
        let url = window.location.href;
        text = text || "";

        let link = this.shareLinks[linkName].replace("{url}", encodeURIComponent(url));
        link = link.replace("{text}", encodeURIComponent(text));

        window.open(link, '', 'left='+left+',top='+top+',width='+this.windowWidth+',height='+this.windowHeight+',personalbar=0,toolbar=0,scrollbars=0,resizable=0');
    }

    facebook() {
        this.openWindow( "facebook" );
    }

    twitter() {
        this.openWindow( "twitter" );
    }
}

new SocialSharing();
