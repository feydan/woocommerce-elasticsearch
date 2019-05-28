<?php

namespace App\Http\Controllers;

use App\Transforms\OrderTransform;
use Elasticsearch;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Woocommerce;

class OrderController extends BaseController
{
    /**
     * Get a single order by id
     *
     * @param Request $request
     * @param $orderId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function get(Request $request, $orderId)
    {
        $fromIndex = $request->get('fromIndex', false);

        $order = $fromIndex ? $this->getOrderFromIndex($orderId) : $this->getOrderFromWoocommerce($orderId);

        return view('order', ['order' => $order]);
    }

    /**
     * Gets an order from the Elasticsearch index
     *
     * @param $orderId
     *
     * @return mixed
     */
    protected function getOrderFromIndex($orderId)
    {
        $params = [
            'index' => config('elasticsearch.defaultIndex'),
            'type' => '_doc',
            'id' => $orderId
        ];

        return Elasticsearch::connection()->get($params)['_source'];
    }

    /**
     * Gets an order from Woocommerce using the api
     *
     * @param $orderId
     *
     * @return array
     */
    protected function getOrderFromWoocommerce($orderId)
    {
        return OrderTransform::transformToElasticsearch(Woocommerce::get("orders/{$orderId}"));
    }
}
