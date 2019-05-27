<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Woocommerce Elasticsearch</title>
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
<div id="app" class="container"></div>
<script src="js/app.js"></script>
</body>
</html>