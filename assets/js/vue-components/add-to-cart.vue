<template>
  <div>
    <form method="POST" @submit.prevent="updateQty" v-bind:class="this.isLoading">
      <input type="hidden" name="action" value="/commerce/cart/update-cart">
      <input type="submit" class="hidden js-hidden-submit">
      <!-- If the add to cart button isn't disabled -->
      <div v-if="disableAttr===false">

        <!-- Add to cart button is a product currently in cart so needs to update a current line item -->
        <div v-if="isInCart">
          <div v-bind:class="'number-field product__button product__button--qty '+this.isLoading">

          <!-- Don't increment qty, just update cart (used when adding gifts to a current line item) -->
          <div v-if="hideqty==='true'">
            <input type="hidden" v-bind:name="`${this.lineItem}[qty]`" v-bind:value="qty">
            <input type="submit" v-bind:value="buttonText" v-bind:class="btn" v-bind:disabled="disableAttr">
          </div>

          <!-- Show qty buttons (used in most cases) -->
          <div v-else>
            <button type="submit" class="number-field__minus minus">-</button>
            <input type="text" v-bind:name="`${this.lineItem}[qty]`" v-bind:value="qty" class="number-field__input">
            <button type="submit" class="number-field__plus plus">+</button>
          </div>
          </div>
        </div>

        <!-- Add to cart button is a new product that hasn't yet been added to cart -->
        <div v-else>
          <input type="hidden" name="purchasableId" v-bind:value="this.thePurchasableid">
          <input type="submit" v-bind:value="buttonText" v-bind:class="btn" v-bind:disabled="disableAttr">
          <input type="hidden" name="qty" value="1" class="number-field__input">
        </div>
      </div>
      <!-- Add to cart button is disabled - this could be for a number of reasons such as out of stock etc.-->
      <div v-else>
        <span disabled="disabled" class="product-form__button product-form__cart-add-btn">Add to cart</span>
      </div>
      <!-- Include gift Options -->
      <div v-for="option, key in theOptions" v-if="typeof option !='undefined'">
      <!--input v-if="option == 'remove'" lineItems[LINE_ITEM_ID][remove]-->
        <input v-if="isInCart" type="hidden" v-bind:name="`${lineItem}[options][${key}]`" v-bind:value="option.name ? JSON.stringify(option) : ''">
        <input v-else type="hidden" v-bind:name="`options[${key}]`" v-bind:value="option.name ? JSON.stringify(option) : ''">
      </div>
    </form>
  </div>
</template>

<script>
  import debounce from 'lodash/debounce';
  import { actions, getters, mutations } from '../store';
  import { CartUpdatedNotification } from '../utilities/notifications/cart-updated';
  import { Notification } from '../utilities/notification';
  import 'url-search-params-polyfill';

  export default {
    props: [
      'productid', // Product ID - Used 
      'purchasableid',
      'itemincart', // this is the line item.
      'note',
      'schedule',
      'notify',
      'btnclass',
      'options',
      'hideqty',
      'disable',
      'title',
      'isgiftoption',
    ],
    name: 'add-to-cart',
    created() {
        this.updateAfterNewVariantSelected();
    },
    methods: {
      updateQty: debounce(async function(e) {
        const data = new URLSearchParams( new FormData(e.target) );
        await actions.updateCart(data, this.notify);
      }, 500),
      /**
      * Gets a line item that matches a purchasable ID.
      * This is so we can sync product qtys when there's more than one of the same product on the page ie. If the user updated a qty in the cart summary when browsing the product listing, the qty for that product in the listing would update too.
      **/
      getLineItem: function(purchasableId) {

        // If this 'add to cart button' is part of a line item.
        if(typeof this.itemincart !="undefined" && Object.keys(this.itemincart).length > 0) {
          return this.itemincart;
        }

        let lineItems = getters.lineItems(); //
        for(var item in lineItems) {
          if(purchasableId == (lineItems[item].purchasableId)) {
            // don't add qty to line items with options
            if(! Object.keys(lineItems[item].options).length > 0) {
              return lineItems[item];
            }
          }
        }
      },

      /**
      * Allow updating this products purchasableID from outside of VUE.
      * Ideally, the product page it's self would be a vue component. This works for now 
      * though.
      */
      updateAfterNewVariantSelected() {
        document.addEventListener('update-add-to-cart-props', (e) => {
          if( e.detail.productid == this.productid ) {

            // If it's a line item (an item in the cart) return.
            if(typeof this.itemincart !="undefined" && Object.keys(this.itemincart).length > 0) {
              return this.itemincart;
            }

            if(e.detail.purchasableid !== null) {
              this.purchasableid = e.detail.purchasableid;
            }


            if(e.detail.disableAttr !== null) {
              this.disable = e.detail.disableAttr;
            }
          }

        }, false);
      }
    },
    computed: {
      btn: function() {
        if(this.btnclass) {
          return this.btnclass;
        }
        return 'product-form__button product-form__cart-add-btn';
      },
      disableAttr: function() {
        return typeof this.disable && this.disable == true ? 'disabled' : false;
      },
      isLoading: function() {
        return getters.isLoading() ? 'add-to-cart--loading' : 'add-to-cart--loaded';
      },
      lineItem: function() {
        let setLineItem = this.getLineItem(this.thePurchasableid);
        if(setLineItem) {
          return 'lineItems['+setLineItem.id+']';
        }
      },
      isInCart: function() {
        let setLineItem = this.getLineItem(this.thePurchasableid);
        return (typeof setLineItem !=="undefined" && typeof setLineItem.id !=="undefined");
      },
      qty: function() {
        let setLineItem = this.getLineItem(this.thePurchasableid);
        if(setLineItem) {
          return setLineItem.qty;
        }
        return 0;
      },
      thePurchasableid: function () {
        return this.purchasableid;
      },
      theOptions: function() {
          try {
            JSON.parse(this.options);
          } catch (e) {
            return {};
          }
          return JSON.parse(this.options);
      },
      buttonText: function() {
        return (this.qty && this.isgiftoption) ? 'Update cart' : 'Add to cart';
      }
    },
  };
</script>