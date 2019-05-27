<template>
    <div id="app" class="container">
        <div class="heading">
            <h1>Woocommerce Elasticsearch</h1>
        </div>
        <input id='search' v-on:keyup="search" placeholder="search">
        <table class="order-table">
            <thead>
                <tr>
                    <th scope="col">id</th>
                    <th scope="col">First</th>
                    <th scope="col">Last</th>
                    <th scope="col">Address</th>
                    <th scope="col">Product</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Price</th>
                </tr>
            </thead>
            <tbody>
                <order-component
                        v-for="order in orders"
                        v-bind="order"
                        :key="order.id"
                ></order-component>
            </tbody>
        </table>
    </div>
</template>

<script>
    import _ from 'lodash';

    function Order(data) {
        this.id = data.id;
        delete data.id;
        this.data = data;
    }

    import OrderComponent from './Order.vue';

    export default {
        data() {
            return {
                orders: [],
                query: '',
                working: false
            }
        },
        methods: {
            search: _.debounce(
                function (val) {
                    this.query = val.target.value;
                    console.log(this.query);
                    this.read();
                },
                250
            ),
            read() {
                this.mute = true;
                window.axios.get('/search?query=' + this.query).then(({ data }) => {
                    var orders = [];
                    data.forEach(order => {
                        console.log(order);
                        var line_items = order.line_items;
                        delete order.line_items;
                        line_items.forEach(line_item => {
                            order.line_item = line_item;
                            orders.push(new Order(order));
                        })
                    });
                    this.orders = orders;
                    this.mute = false;
                });
            },
        },
        watch: {
            mute(val) {
                document.getElementById('mute').className = val ? "on" : "";
            }
        },
        components: {
            OrderComponent
        },
        created() {
            this.read();
        }
    }
</script>
<style>
    .heading h1 {
        margin-bottom: 0;
    }
    #search {
        margin-top: 1em;
    }
    .order-table {
        margin-top: 1em;
    }
</style>