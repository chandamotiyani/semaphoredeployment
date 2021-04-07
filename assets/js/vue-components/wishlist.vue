<template>
  <div>
  <div v-if="wishlistItems.length">
    <ul v-bind:class="`cart-summary__cart-items cart-summary__cart-items--${this.isLoading}`">
      <li v-for="item in wishlistItems" v-bind:class="'cart-item__product'" v-bind:id="'wishlist-item--'+item.id">
        <!-- Chanda - Linking product detail page -->
        <a v-bind:href="item.productUrl"><img v-bind:src="item.imageUrl" class="cart-item__image"></a>
        <div class="cart-item__content">
            <!-- Chanda - Linking product detail page -->
          <a v-bind:href="item.productUrl">
            <div class="cart-item__detail">
              <h3 class="cart-item__heading">{{ item.title }}</h3>
              <strong class="cart-item__sub-heading"></strong>
            </div>
          </a>
          <div class="cart-item__options">
            <div class="cart-item__right">
              <div class="cart-item__price">{{ item.price }}</div>
              <add-to-cart v-bind:disable="item.disableBuyButton" v-bind:purchasableid="item.purchasableId" @click="addedToCart(item.id)"></add-to-cart>
              <span class="cart-item__remove" @click="removeItem(item.id, item.listId)">Remove</span>
            </div>
          </div>
        </div>
      </li>
    </ul>
  </div>
  <div v-else>
    <p class="wishlist-text">You currently have no items in your wishlist. <br>To add items and save to view later, click the <svg class="product-form__wishlist product-form__wishlist--active"><use xlink:href="#heart"></use></svg> on your favourite wines.</p>
    <br><br>
    <a href="/shop/" class="boxed-button">Visit the shop</a>
  </div>
    <div v-if="wishlistItems.length" class="cart-summary__continue">
      <a href="/shop/wishlist" class="cart-summary__continue-link">View Items</a>
    </div>
  </div>
</template>

<script>
  import { getters, mutations, actions } from '../store';
  import addToCart from './add-to-cart.vue';

  export default {
    name: 'wishlist-summary',
    components: {
      addToCart
    },
    removeItems: [],
    created: async () => {
      const updateCart = actions.updateWishlistItems();
    },
    methods: {
      async removeItem(lineItemId, listId) {

        this.$options.removeItems.push(lineItemId);

        this.beforeRemoveItem();

        const removeItem = await actions.removeWishlistItems(this.$options.removeItems, listId);

        this.afterRemoveItem();

        this.$options.removeItems = getters.removeWishlistItems();
      },
      beforeRemoveItem() {
        this.$options.removeItems.forEach((lineItem) => {
          let wishlistItem = document.getElementById('wishlist-item--'+lineItem);
          if(typeof wishlistItem !='undefinied' && wishlistItem !=null){
            wishlistItem.classList.add('cart-item__product--removed');
          }
        });
      },
      afterRemoveItem() {
        // Temp fix for some false positives. Remove should return 
        let removed = document.querySelectorAll('.cart-item__product--removed');

        if(typeof removed !== "undefined" && removed !=null) {
          removed.forEach(function(removeItem) {
            removeItem.classList.remove('cart-item__product--removed');
          });
        }

      },
      addedToCart(item) {
          // Animation for Add to cart
          let wishlistItem = document.getElementById('wishlist-item--'+lineItem);
          if(typeof wishlistItem !='undefined' && wishlistItem !=null){
            wishlistItem.classList.add('cart-item__product--added-to-cart');
          }
      }
    },
    computed: {
      wishlistItems: function() {
        return getters.wishlistItems();
      },
      wishlistCount: {
        get: function() {
          return getters.wishlistItems().length;
        }
      },
      isLoading: function() {
        return getters.isLoading() ? 'loading' : 'loaded';
      }
    },
    watch: {
      wishlistCount: function(count) {
        let wishCountEl = document.querySelectorAll('.js-wishlist-items');
        if(typeof wishCountEl !== "undefined" && typeof wishCountEl != null) {
          wishCountEl.forEach(function(item) {
          item.innerHTML = count;
          });
        }
      }
    }
  };
</script>