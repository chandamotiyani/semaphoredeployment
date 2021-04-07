<template>
  <div>
    <a v-on:click="addGiftOptions(productid, lineitemid, thePurchasableid)" href="#add-gift" class="product-form__button product-form__cart-add-btn" v-bind:disabled="disableAttr">{{ text }}</a>
  </div>
</template>

<script>
  import { actions, getters } from '../store';

  export default {
    props: [
      'productid',
      'disable',
      'hasgiftoptions',
      'buttontext',
      'lineitemid', // If this component is clicked from a line item
      'purchasableid',
      ],
    name: 'add-gift-option-button',
    data: function() {
      return {
        thePurchasableid: this.purchasableid,
      }
    },
    created() {
        this.updateAfterNewVariantSelected();
    },
    computed: {
      disableAttr: function() {
        return typeof this.disable && this.disable == true ? 'disabled' : false;
      },
      isLoading: function() {
        return getters.isLoading() ? 'add-to-cart--loading' : 'add-to-cart--loaded';
      },
      text: function() {
        let giftoptionsText = this.buttontext ? this.buttontext : 'Add as gift';
        return this.hasgiftoptions ? 'Edit gift options' : giftoptionsText;
      }
    },
    methods: {
      addGiftOptions(productId, lineitemId, purchasableId) {
        actions.getGiftOptions(productId, lineitemId, purchasableId);
      },
      updateAfterNewVariantSelected() {
        document.addEventListener('update-add-to-cart-props', (e) => {
          if( e.detail.productid == this.productid ) {

            // If it's a line item (an item in the cart) return.
            if(typeof this.lineitemid !="undefined" || ! this.lineitemid) {
              if(e.detail.purchasableid !== null) {
                this.thePurchasableid = e.detail.purchasableid;
                this.disable = e.detail.giftOptionsDisabled;
              }
            }
          }

        }, false);
      }
    },
  };
</script>