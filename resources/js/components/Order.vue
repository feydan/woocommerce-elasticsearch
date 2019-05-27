<template>
    <tr @click="openOrder(id)">
        <th scope="row">{{ id }}</th>
        <td>{{ data.shipping_first_name }}</td>
        <td>{{ data.shipping_last_name }}</td>
        <td>{{ data.shipping_address_1 }}, {{ data.shipping_city }}, {{ data.shipping_state }} {{ data.shipping_postcode }}</td>
        <td>{{ data.line_item.name }}</td>
        <td>{{ data.line_item.quantity }}</td>
        <td>{{ data.line_item.price }}</td>
    </tr>
</template>

<script>
    export default {
        methods: {
            openOrder(id) {
                window.open('/order/' + id);
            },
        },
        props: ['id', 'data']
    }
</script>
