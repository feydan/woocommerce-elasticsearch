<?php

namespace App\Transforms;

class OrderTransform
{
    /**
     * Transforms an order from the Woocommerce api into Elasticsearch doc format
     *
     * @param array $woocommerceOrder
     *
     * @return array
     */
    public static function transformToElasticsearch(array $woocommerceOrder): array
    {
        return [
            'id' => $woocommerceOrder['id'],
            'shipping_first_name' => $woocommerceOrder['shipping']['first_name'],
            'shipping_last_name' => $woocommerceOrder['shipping']['last_name'],
            'shipping_address_1' => $woocommerceOrder['shipping']['address_1'],
            'shipping_city' => $woocommerceOrder['shipping']['city'],
            'shipping_state' => $woocommerceOrder['shipping']['state'],
            'shipping_postcode' => $woocommerceOrder['shipping']['postcode'],
            'line_items' => array_map(
                function ($lineItem) {
                    return [
                        'name' => $lineItem['name'],
                        'price' => (float) $lineItem['total'],
                        'quantity' => (int) $lineItem['quantity'],
                    ];
                },
                $woocommerceOrder['line_items']
            ),
        ];
    }
}
