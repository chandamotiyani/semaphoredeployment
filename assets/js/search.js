import Vue from 'vue';
import debounce from 'lodash/debounce';

export const state = Vue.observable({
    searchSuggestions: [],
    recomWines : [],
    recomEvent : [],
    recomArchive : [],
    recomBackVintages: [],
    keywordChars: 0,
    isLoading: false,
});

export const mutations = {
    setSearchSuggestions: (data) => state.searchSuggestions = data,
    setRecomWines: (data) => state.recomWines = data,
    setRecomEvent: (data) => state.recomEvent = data,
    setRecomArchive: (data) => state.recomArchive = data,
    setKeywordChars: (length) => state.keywordChars = length,
    setrecomBackVintages: (data) => state.recomBackVintages = data,
    setIsLoading: (val) => state.isLoading = val,
};

export const getters = {
    searchSuggestions: () => state.searchSuggestions,
    recomWines :() =>  state.recomWines,
    recomEvent :() =>  state.recomEvent,
    recomArchive :() => state.recomArchive,
    keywordChars: () => state.keywordChars,
    recomBackVintages: () => state.recomBackVintages,
    isLoading: () => state.isLoading,
}

export const actions = {

    async getSuggRecomSearch(phrase, abortCtrl) {

        mutations.setIsLoading(true);
        let state = document.getElementsByName('state')[0].value;
        let limit = 2;
        let recomWines= [], recomEvent =[], recomArchive = [], recomBackVintages = [];

        let suggestedKeywords = await this.search('suggestion.keywords', phrase, 3, state, abortCtrl);
        mutations.setSearchSuggestions(suggestedKeywords);

        if(state == 'shop' || state == 'website') {
            recomWines = await this.search('products', phrase, limit, state, abortCtrl);
            mutations.setRecomWines(recomWines);

            recomEvent = await this.search('events', phrase, limit, state, abortCtrl);
            mutations.setRecomEvent(recomEvent);
        }
        if (state == 'archives' || state == 'website') {
            recomArchive = await this.search('entries', phrase, limit, state, abortCtrl);
            mutations.setRecomArchive(recomArchive);

            recomBackVintages = await this.search('backVintages', phrase, limit, state, abortCtrl);
            mutations.setrecomBackVintages(recomBackVintages);
        }

       // mutations.setRecomWines(recomWines);
       // mutations.setRecomEvent(recomEvent);
       // mutations.setRecomArchive(recomArchive);
       // mutations.setSearchSuggestions(suggestedKeywords);
       // mutations.setrecomBackVintages(recomBackVintages);

       mutations.setIsLoading(false);
    },

    async search(endpoint, phrase, limit, state, abortCtrl = new AbortController()) {

        if(typeof phrase !== "undefined" && phrase.length >= 1) {

            let response = await fetch(`${window.location.origin}/search.${endpoint}?phrase=${phrase}&limit=${limit}&state=${state}`, {
                method: 'GET',
                signal: abortCtrl.signal,
                headers: {
                    "Content-Type": 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    //'X-CSRF-Token': window.csrfTokenValue
                },
            });

            let getJSON = await response.json();

            return getJSON.data;
        }
        return [];
    },

    countKeywordChars(phrase) {
        mutations.setKeywordChars(phrase.length);
    },

    async getDefaultSugg() {
        let state = document.getElementsByName('state')[0].value;
        let suggestedKeywords = await this.search('suggestion.keywords', 'default', 3, state);
        mutations.setSearchSuggestions(suggestedKeywords);
    },

    /*async getSearchSuggestions(phrase) {
        //Please Select;
        let state = document.getElementsByName('state')[0].value;
        state = "website";

        let combine, proSuggestions, eventSuggestions, entriesSuggestions = [];
        let left_suggestions = 3;

        if(state == 'shop' || state == 'website') {
            proSuggestions = await this.searchProducts(phrase, left_suggestions);
            console.log(proSuggestions);
            left_suggestions -= proSuggestions.length;
            //if the suggestions didn't found for products or matches are less than 3 then we can search for events and push them in the products list

            if(proSuggestions.length < 3) {
                eventSuggestions = await this.searchEvent(phrase, left_suggestions);
                left_suggestions -= eventSuggestions.length;
            }
            combine = proSuggestions.concat(eventSuggestions);
        }

        if(((combine && combine.length < 3) || (state == 'archive' || state == 'website')) && state != 'shop') {
            entriesSuggestions = await this.searchEntries(phrase, left_suggestions);
            combine = combine.concat(entriesSuggestions)
        }

        mutations.setSearchSuggestions(combine);
    },*/
}

