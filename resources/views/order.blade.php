<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Woocommerce Order #{{ $order['id'] }}</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;,
            height: 100%;
            width: 100%;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="/css/app.css">
</head>
<body>
<div id="app" class="container">
    <div class="heading">
        <h1>Woocommerce Order #{{ $order['id'] }}</h1>
        <a href="/">Back to search</a>
    </div>
    <div class="row">
        <div class="col-sm">
            Order Id
        </div>
        <div class="col">
            #{{ $order['id'] }}
        </div>
    </div>
    <div class="row">
        <div class="col-sm">
            Shipping First Name
        </div>
        <div class="col">
            {{ $order['shipping_first_name'] }}
        </div>
    </div>
    <div class="row">
        <div class="col-sm">
            Shipping Last Name
        </div>
        <div class="col">
            {{ $order['shipping_last_name'] }}
        </div>
    </div>
    <div class="row">
        <div class="col-sm">
            Shipping Address
        </div>
        <div class="col">
            {{ $order['shipping_address_1'] }}, {{ $order['shipping_city'] }}, {{ $order['shipping_state'] }} {{ $order['shipping_postcode'] }}
        </div>
    </div>
    @foreach ($order['line_items'] as $lineItem)
        <div class="row">
            <div class="col-sm">
                Product Name
            </div>
            <div class="col">
                {{ $lineItem['name'] }}
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                Product Quantity
            </div>
            <div class="col">
                {{ $lineItem['quantity'] }}
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                Product Price
            </div>
            <div class="col">
                {{ $lineItem['price'] }}
            </div>
        </div>
    @endforeach
</div>
</body>
</html>