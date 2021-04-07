<template>
  <div v-if="typeof lineItems !='undefined'">

    <div v-for="group in itemGroup()">
      <h3 class="checkout__col-sub-heading" v-if="group.lineItems.length > 0 && group.title">{{ group.title }}</h3>
      <ul class="cart-summary__cart-items" data-total-cart-qty="13">
        <li class="cart-item__product" v-for="item in sortLineItemsByDate(group.lineItems)" v-bind:id="'line-item--'+item.id">
          <a v-if="typeof item.snapshot.product !='undefined'" v-bind:href="item.snapshot.url">
            <img v-bind:src="item.snapshot.product.image" class="cart-item__image"/>
          </a>
          <div class="cart-item__form cart-item--modal">
            <div class="cart-item__content">
              <a v-bind:href="item.snapshot.url">
                <div class="cart-item__detail">
                  <h3 class="cart-item__heading">{{ item.snapshot.title }}</h3>
                  <strong class="cart-item__sub-heading"></strong>
                  <span v-if="typeof item.snapshot.bottleSize != 'undefined'" class="heading-secondary-sm">Bottle Size: {{ item.snapshot.bottleSize }}</span>

                  <div v-if="item.onSale" class="heading-secondary-sm" style="margin-top: 6px; margin-bottom: 5px;">(Sale Price: 
                    <strike>{{ formatPrice(item.price) }}</strike>
                    {{ formatPrice(item.salePrice) }})
                  </div>
                </div>
              </a>
              <div class="cart-item__options">
                <add-to-cart v-bind:itemincart="item" v-bind:purchasableid="item.purchasableId" v-bind:options="item.options" v-bind:note="item.note"></add-to-cart>
                <div class="cart-item__right">
                  <div class="cart-item__price">{{ formatPrice(item.total) }}</div>
                  <a v-on:click="removeItem(item.id)" class="cart-item__remove">Remove</a>
                </div>
              </div>
            </div>
            <ul v-for="option in item.options" v-if="option !=''" class="cart-item__content cart-item__content--adjustments cart-item__options-list">
              <li>
                <div>
                  {{ parseOption(option).name }}
                  <br><span v-if="option && parseOption(option).note" class="cart-item__product-note">&ldquo;{{ parseOption(option).note }}&ldquo;</span>
                </div>
              </li>

            </ul>

            <div v-if="typeof item.snapshot.product !=='undefined' && item.snapshot.product.has_gift_options">
              <add-gift-option-button 
              v-bind:productid="item.snapshot.product.id" 
              v-bind:purchasableid="item.purchasableId" 
              v-bind:hasgiftoptions="Object.keys(item.options).length" 
              v-bind:lineitemid="item.id"
              v-bind:buttontext="'Make item a gift'
              "></add-gift-option-button><br>
            </div>


            <ul v-if="item.snapshot.startDateTime" class="cart-item__options-list">
              <li>No. of Tickets: {{ item.qty }}</li>
              <li>Date: {{ formatDate(item.snapshot.startDateTime, 'MMMM Do YYYY') }}</li>
              <li>Time: {{ formatDate(item.snapshot.startDateTime, 'h:mm a') }}</li>
            </ul>
          </div>
        </li>
      </ul>
    </div>
    <div class="cart-totals cart-totals--modal">
      <div class="cart-totals__totals">
        <h3 class="cart-totals__totals-heading">Total</h3>
        <div class="cart-totals__totals-price">{{ total }}</div>
      </div>
    </div>
  </div>
</template>

<script>
  import { getters, mutations, actions } from '../store';
  import addToCart from './add-to-cart.vue';
  import addGiftOptionButton from './add-gift-option-button.vue';

  import moment from 'moment';

  export default {
    name: 'cart-summary',
    created: async () => {
      const updateCart = await actions.getCart();
    },
    props:[ 'validate', 'group' ],
    data: function() {
      return {
        validated:[]
      }
    },
    components: {
      addToCart,
      addGiftOptionButton,
    },
    computed: {
      /*
      * Get the line items in the cart
      */
      lineItems: function() {
        return getters.lineItems();
      },
      adjustments: function() {
        return getters.adjustments();
      },
      total: function() {
        return this.formatPrice(getters.total());
      },
      /*
      * Gets GIFT OPTIONS from `/products/gift-options/${productId}/${lineitemId ? lineitemId : ''}` 
      * Gift options are gift options set against the product in the admin.
      */
      giftOptions: function() {
        return getters.giftOptions();
      }
    },
    watch: {
      lineItems: function(){
        if(this.validate){
          this.validateLineItems();
        }
      }
    },
    methods: {
      validateLineItems() {
        const entries = Object.entries(this.lineItems);
        for (const [key, element] of entries) {
          if(this.validated.indexOf(key) === -1){
            if(element.snapshot.noticeDateTime){
              if(moment(element.snapshot.noticeDateTime.date).isBefore(moment())){
                if (confirm("Looks like an event is past the deadline. Click ok to remove the item.")) {
                  this.removeItem(key);
                  this.validated.push(key);
                } else {
                  //do nothing
                  this.validated.push(key);
                }
                return element;
              }
            }
          }
        }
      },

      itemGroup() {

        let items = {
          shop: {
            title: 'The Wine Shop',
            lineItems: [],
          },
          events: {
            title: 'Events',
            lineItems: [],
          },
          tours: {
            title: 'Winery Experiences',
            lineItems: [],
          }
        };


        let keys = Object.keys(this.lineItems);
        if(keys) {
          keys.forEach(key => {
            let lineItem = this.lineItems[key];

            if(lineItem.snapshot.groupHandle =='events') {
              items.events.lineItems.push(lineItem);
            }
            else if(lineItem.snapshot.groupHandle =='tours' || lineItem.snapshot.groupHandle =='tastings') {
              items.tours.lineItems.push(lineItem);
            }
            else { // wine shop
              items.shop.lineItems.push(lineItem);
            }
          });
        }

        if(this.group) {
          return items;
        } else {
          return {
            all: {
              title: '',
              lineItems: this.lineItems,
            },
          }
        }
      },
      async removeItem(lineItemId) {

        document.getElementById('line-item--'+lineItemId).classList.add('cart-item__product--remove');

        const data = {
          qty: 0,
          lineItemId: lineItemId,
          action: '/commerce/cart/update-cart',
        };
        data['lineItems['+lineItemId+'][remove]'] = true; // posting a value to this input name removes item.

        const updateCart = await actions.updateCart(data);

        let remove = document.querySelectorAll('.cart-item__product--remove');
        try {
          remove.forEach(function(el) {
            el.classList.remove('cart-item__product--remove')
          });
        } catch {}

      },
      formatDate(date, format) {
        return moment(date).format(format);
      },
      formatPrice(price) {
        return new Intl.NumberFormat('en-AU', { style: 'currency', currency: 'AUD' }).format(price);
      },

      /**
      * Parse gift options - these will be in the form of a json stringified object.
      */
      parseOption(options) {
          try {
              JSON.parse(options);
          } catch (e) {
              return {};
          }
          return JSON.parse(options);
      },

      sortLineItemsByDate(lineItems) {
        let items = lineItems.slice(0);

        return items.sort((a, b) => (a.dateCreated < b.dateCreated) ? 1 : -1);
      }
    }
  };
</script>