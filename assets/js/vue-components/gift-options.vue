<!--
1: Gift options get loaded here based on the productid prop.
2: In the front end, User chooses gift options or adds a note from checkbox / textarea field
3: That field data gets turned into a JSON Object and stringified.
4: Data gets sent into to an Add to Cart Button Component.
-->
<template>
  <div class="add-gift__col add-gift__col modal__col">
    <h2 class="add-gift__heading add-gift__title modal__heading">Create a <em>beautiful</em> gift</h2>
    <div class="add-gift__product-main">
      <strong class="add-gift__product-main-title">{{ giftOptions.title }}</strong>
      <span class="add-gift__product-main-price">${{ giftOptions.price }}</span>
    </div>
    <div v-if="isLoading == false">
      <div v-if="typeof giftOptions.giftOptions !='undefined'">
        <ul class="gift-items" v-if="giftOptions.giftOptions.length">
          <li v-for="item in giftOptions.giftOptions" class="gift-items__product">
            <div class="gift-items__row">
              <img v-bind:src="item.imageUrl" class="gift-items__image">
              <div class="gift-items__product-detail-wrap">
                <div class="gift-items__product-detail">
                    <h3 class="gift-items__product-heading">{{ item.title }}</h3>
                    <p class="gift-items__product-sub-text">{{ item.description }}</p>
                  </div>
                  <div class="gift-items__product-options">
                    <div class="gift-items__product-right">
                      <div v-if="! item.messageField && typeof options[item.id] !=='undefined'" class="form__input-wrap checkout__input-wrap">
                        <label class="form__checkbox">

                          <input
                          v-model="options[item.id].name"
                          ref="check"
                          @change="checkedGiftOptions($event, `${item.title} (${item.price})`, item.id)" type="checkbox">
                          <span class="form__checkmark"></span>
                        </label>
                      </div>
                      <div class="gift-items__product-price">{{ item.price }}</div>
                    </div>
                  </div>
              </div>
            </div>
            <div v-if="item.messageField && typeof options[item.id] !='undefined'" class="form__input-wrap form__input-wrap">
              <textarea 
              v-model="options[item.id].note"
              placeholder="Enter your message"
              class="form__form-input form__input form__input--textarea" 
              @change="checkedGiftOptions($event, `${item.title} (${item.price})`, item.id)" >
              </textarea>
            </div>
          </li>
        </ul>
        <div v-else>
          <p class="center">No gift options exist for this variation.</p>
        </div>
      </div>
    </div>
    <div v-else>
      Please Wait...
    </div>
    <div class="add-gift__continue">
      <add-to-cart 
        v-bind:productid="giftOptions.id"
        v-bind:purchasableid="giftOptions.purchasableId"
        v-bind:itemincart="giftOptions.lineItem"
        v-bind:options="JSON.stringify(this.checkgiftoptions)"
        v-bind:isgiftoption=true
        class="add-gift__continue-button"
        hideqty=true
        notify="panel">
      </add-to-cart>
    </div>
  </div>
</template>


<script>
  import { getters, mutations, actions } from '../store';
  import addToCart from './add-to-cart.vue';
  import moment from 'moment';
  var assign = require('object.assign/polyfill')();

  export default {
    name: 'gift-options',
    props: ['productid', 'lineitemid'],
    components: {
      addToCart
    },
    watch: {
      // React to changes in Gift Options
      $giftOptions: function(giftOption){
        let options = [];
        this.giftOptions = giftOption;
        this.hasCurrentlySelectedOption = false;

        if(! Object.keys(giftOption).length) {
          return;
        }

        for(let item of giftOption.giftOptions) {
          options[item.id] = {
            'note': '',
            'name': '',
          }

          if( typeof giftOption.lineItem.options[item.id] && giftOption.lineItem.options[item.id] ) {
            options[item.id].note = JSON.parse(giftOption.lineItem.options[item.id]).note;
            options[item.id].name = JSON.parse(giftOption.lineItem.options[item.id]).name ? JSON.parse(giftOption.lineItem.options[item.id]).name : '';
          }

          if(options[item.id].name !=='') {
            this.hasCurrentlySelectedOption = true;
          }
        }
        this.options = options;

        this.checkgiftoptions = assign({}, options, {  });
        
       // this.checkgiftoptions = options;
      },
      $isLoading: function(isLoading) {
        this.isLoading = isLoading;
      }
    },
    data() {
      return {
        checkgiftoptions: [],
        note: '',
        options: [],
        giftOptions: [], // this will change when this modal is opened.
        isLoading: true,
        hasCurrentlySelectedOption: false,
      }
    },
    methods: {
      // These are the options that are getting passed into AddToCart
      checkedGiftOptions(e, title, itemId) {

        // get current options
        let options = assign({}, this.checkgiftoptions, {  });


        // add to current options
        // The value of the checkboxed item is the option ID
        if(e.srcElement.value) {
          options[itemId] = {
            'name': title,
            'note': e.srcElement.type == 'textarea' ? e.srcElement.value : '',
          };
        }

        /**
        * Set the values to false if they're unchecked or empty.
        */
        if(e.srcElement.type == 'checkbox' && e.srcElement.checked == false) {
          options[itemId] = false;
        }

        if(e.srcElement.type == 'textarea' && e.srcElement.value == '') {
          options[itemId] = false;
        }

        this.checkgiftoptions = options;
      },
    }
    //v-bind:checked="typeof giftOptions.lineItem !=null && typeof giftOptions.lineItem !='undefined' && typeof giftOptions.lineItem.options[item.id] !=='undefined'"
  };
</script>