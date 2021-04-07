<template>
    <div>
        <div id="wrapper">
            <label>Search</label>
            <!--<input v-model="filter">-->
            <input v-model="filter" type="search" class="search__form-input form__input" name="search" placeholder="Search Here">
            <h4 v-if="loading" class="loading">Loading</h4>
            <h4 v-else>{{ !!filter ? 'Search Results' : 'Recent Search' }}</h4>
            <ul>
                <li v-for="(item, index) in search" :key="index">
                    <p>Product Name: {{ item.name }}</p>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'example',
        props: [
            'filter',
            'items',
            'loading',
        ],

        computed: {
            search() {
                if (!this.items) {
                    return []
                }

                if (!this.filter) {
                    return this.items.recent
                }

                return this.items.result.filter(item => item.name.includes(this.filter))
            }
        },
        mounted() {
            this.filter = ''
            this.items = []
            this.loading = true
            this.fetchData().then(() => {
                this.loading = false
            })
        },
        methods: {
            async fetchData() {

                // ajax call to fetch items
                console.log(this.filter);

                return new Promise((resolve, reject) => {
                    setTimeout(() => {
                        this.items = {
                            "result": [{
                                name: 'Shiraz',
                            },
                                {
                                    name: 'Port',
                                }]
                        }
                        resolve()
                    }, 3000)
                })
            }
        },
    };
</script>