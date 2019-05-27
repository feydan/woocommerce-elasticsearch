<?php

namespace App\Http\Controllers;

use App\Transforms\OrderTransform;
use Elasticsearch;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Woocommerce;

class OrderController extends BaseController
{
    public function get(Request $request, $orderId)
    {
        $fromIndex = $request->get('fromIndex', false);

        $order = $fromIndex ? $this->getOrderFromIndex($orderId) : $this->getOrderFromWoocommerce($orderId);

        return view('order', ['order' => $order]);
    }

    protected function getOrderFromIndex($orderId)
    {
        $params = [
            'index' => config('elasticsearch.defaultIndex'),
            'type' => '_doc',
            'id' => $orderId
        ];

        return Elasticsearch::connection()->get($params)['_source'];
    }

    protected function getOrderFromWoocommerce($orderId)
    {
        return OrderTransform::transformToElasticsearch(Woocommerce::get("orders/{$orderId}"));
    }
}
