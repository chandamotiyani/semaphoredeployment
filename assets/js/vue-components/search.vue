<template>

    <div>
        <input v-model="filter"  id="search-input" type="search" class="search__form-input form__input"
               name="search" autocomplete="yalumbafalse" placeholder="Search Here" @keyup.enter="registerGA(filter, filter)">
        <p v-if="isLoading">Searching...</p>
        <div class="search__results-container">
            <div class="search__results">
                <div v-if="typeof searchSuggestions !== 'undefined' && searchSuggestions.length">
                    <h2 class="search__results-heading">Suggested Searches</h2>
                    <div v-for="suggestion in searchSuggestions.slice(0,3)">
                        <a v-bind:href="suggestion.sUrl" class="search__results-result" v-on:click="registerGA(suggestion.url, suggestion.title)">{{suggestion.title}}</a>
                    </div>
                </div>
                <div v-if="typeof searchSuggestions !== 'undefined' && keywordChars >= 3 && searchSuggestions.length <= 0">
                    <h2 class="search__results-heading">Suggested Searches</h2>
                    <div>
                        <p>No matches found.</p>
                    </div>
                </div>
            </div>

            <h2 class="search__results-heading" v-if="recomWines.length || recomEvent.length || recomArchive.length || recomBackVintages.length">Recommended Searches</h2>

            <div class="search__results" v-if="recomWines.length">
                <strong class="search__results-sub-heading">Wine Shop</strong>
                <div v-for="wine in recomWines">
                    <a v-bind:href="wine.url" class="search__results-result" v-on:click="registerGA(wine.url, wine.title)">{{wine.title}}</a>
                </div>
            </div>

            <div class="search__results" v-if="recomEvent.length">
                <strong class="search__results-sub-heading">Events</strong>
                <div v-for="event in recomEvent">
                    <a v-bind:href="event.url" class="search__results-result" v-on:click="registerGA(event.url, event.title)">{{event.title}}</a>
                </div>
            </div>

            <div class="search__results" v-if="recomArchive.length">
                <strong class="search__results-sub-heading">Pages</strong>
                <div v-for="archive in recomArchive">
                    <a v-bind:href="archive.url" class="search__results-result" v-on:click="registerGA(archive.url, archive.title)">{{archive.title}}</a>
                </div>
            </div>

            <div class="search__results" v-if="recomBackVintages.length">
                <strong class="search__results-sub-heading">Back Vintages</strong>
                <div v-for="bv in recomBackVintages">
                    <a v-bind:href="bv.url" class="search__results-result" v-on:click="registerGA(bv.url, bv.title)">{{bv.title}}</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {actions, getters} from '../search';

    export default {
        name: "search",
        props: ['filter'],
        abortController: new AbortController(),

        data() {
            return {
                filter: "",
                awaitingSearch: false,
            };
        },

        // Chanda - send request to server when user stops typing or if 1 second is passed, to reduce the requests been sent
        watch: {
            filter: function (val) {

                this.$options.abortController.abort();

                this.$options.abortController = new AbortController();

                actions.countKeywordChars(val);
                actions.getSuggRecomSearch(val, this.$options.abortController);
            },
        },
        // end

        // our methods
        methods: {
            registerGA: function(url, searchTerm) {
                if(window.ga && ga.create) {
                    ga('send', 'page', searchTerm);
                }
            },

            getDefaultSugg: function() {
                actions.getDefaultSugg();
            }
        },
        mounted() {
            actions.getDefaultSugg();
        },
        computed: {
            searchSuggestions: function () {
                return getters.searchSuggestions();
            },
            keywordChars: function () {
                return getters.keywordChars();
            },
            recomWines: function () {
                return getters.recomWines();
            },
            recomEvent: function () {
                return getters.recomEvent();
            },
            recomArchive: function () {
                return getters.recomArchive();
            },
            recomBackVintages: function() {
                return getters.recomBackVintages();
            },
            isLoading: function() {
                return getters.isLoading();
            }
        }
    }
</script>

<style scoped>

</style>
