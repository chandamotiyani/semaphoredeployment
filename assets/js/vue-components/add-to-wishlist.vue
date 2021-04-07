<template>
  <div class="product-form__list-group">
    <span class="product-form__button" v-on:click="toggleWishlist">
      <svg v-bind:class="classes"><use xlink:href="#heart"></use></svg>
    </span>
  </div>
</template>

<script>
  import debounce from 'lodash/debounce';
  import { actions, getters } from '../store';
  import { Notification } from '../utilities/notification';

  export default {
    props: ['purchasableid', 'inwishlist', 'wishlisttoggleurl'],
    name: 'add-to-wishlist',
   data: function() {
      return {
        waiting: false,
      }
    },
    computed: {
      classes: function() {
        let classes = ['product-form__wishlist'];

        if(this.isinwishlist) {
          classes.push('product-form__wishlist--active');
        }

        if(this.waiting) {
          classes.push('product-form__wishlist--waiting');
        }

        return classes;
      },
      isinwishlist: function() {
        let wishlistItems = getters.wishlistItems();

        let itemExists = false;
        wishlistItems.forEach((item) => {
           if(item.purchasableId == this.purchasableid) {
             itemExists = true;
           }
        });

        return itemExists;
      }
    },
    methods: {
      toggleWishlist: debounce(async function(e) {
        this.waiting = true;
        let updateWishlist = await actions.toggleWishlist(this.wishlisttoggleurl);

        new Notification({ type: 'add-to-cart', message: this.isinwishlist ? 'Added to Wishlist' : 'Removed From Wishlist' });

        await actions.updateWishlistItems();

        this.waiting = false;
      }, 500),
    }
  };
</script>