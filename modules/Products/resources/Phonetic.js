if (typeof Craft.Products === typeof undefined) {
    Craft.Products = {};
}


(function($) {



    Craft.Products.Phonetic = Garnish.Base.extend({
        id:null,
        $container:null,
        $fieldContainer:null,
        $unitOfMeasureInput:null,
        $unitOfMeasure:null,
        $itemsPerCase:null,
        $jdeProductNumber:null,

        init: function (elementType, $container, settings) {
            this.$container = $('#main-container'); //TODO: revisit this - this is real gross.
            this.$fieldContainer = $('#'+elementType+'-field');
            this.$element = $('select', this.$fieldContainer);
            this.$unitOfMeasure = $("[id$='unitOfMeasure']", this.$fieldContainer.parent());
            this.$itemsPerCase = $("[id$='itemsPerCase']", this.$fieldContainer.parent());
            this.$jdeProductNumber = $("[id$='jdeProductNumber']", this.$fieldContainer.parent());
            this.$form = $('#main-form');
            //this.addListener(this.$element, 'change', 'changePhonetic');
            this.addListener(this.$form, 'submit', 'onFormSubmit');
            this.$element.selectize({
                create: false,
                sortField: 'text',
                onChange: function(value){
                    this.changePhonetic(this.$element, value);
                }.bind(this)
            });
            //this.$element.selectize.on('change', this.changePhonetic(this.$element));
        },

        onFormSubmit: function(e){
            //sorry for the magic number in here!
            if($("[name='typeId']",this.$form).val() != 5){ //5 is wine packs and gifts. Doesn't require the phonetic check.
                if(this.$element.length){
                    if(!this.$element.val()){
                        if (confirm("Warning: No phonetic entered; if this is a physical product, it will not be available for sale until you select a phonetic.")) {
                            return true;
                        }else{
                            e.preventDefault();
                        }
                    }
                }
            }
        },

        changePhonetic: function ($element, value) {

            $.get('/admin/products/?phonetic='+value, function(data){
                this.$unitOfMeasure.val(data.primaryUnit);
                this.$itemsPerCase.val(data.primaryUnitsPerSecondary);
                this.$jdeProductNumber.val(data.jdeProductNumber);
            }.bind(this));

            $sku = $("[name$='sku]']", $('#details'));
            if($sku.length == 0){
                $sku = $("[name$='sku]']", $element.closest('.fields'))
            }
            $sku.val(this.uniqid(value+'-'));
        },

        /**
         * generates a (probably) unique string for the sku.
         * @param prefix
         * @param moreEntropy
         * @returns {string}
         */
        uniqid: function (prefix, moreEntropy) {
            if (typeof prefix === 'undefined') {
                prefix = ''
            }

            var retId;
            var _formatSeed = function (seed, reqWidth) {
                seed = parseInt(seed, 10).toString(16); // to hex str
                if (reqWidth < seed.length) {
                    // so long we split
                    return seed.slice(seed.length - reqWidth)
                }
                if (reqWidth > seed.length) {
                    // so short we pad
                    return Array(1 + (reqWidth - seed.length)).join('0') + seed
                }
                return seed
            };

            // start with prefix, add current milliseconds hex string
            retId = prefix;
            retId += _formatSeed(parseInt(new Date().getTime() / 1000, 10), 3);
            // add seed hex string
            retId += _formatSeed(Math.floor(Math.random() * 0x75bcd15), 3);
            if (moreEntropy) {
                // for more entropy we add a float lower to 10
                retId += (Math.random() * 10).toFixed(8).toString();
            }

            return retId
        }

    });

})(jQuery);