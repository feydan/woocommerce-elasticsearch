# Woocommerce Elasticsearch
This project demonstrates live search using Elasticsearch with a Woocommerce api integration for fetching data and indexing it into Elasticsearch

## Requirements on host system
- Docker and docker-compose
- Node (v8) / NPM

## Installation
Docker-compose is configured to install the Nginx web server, php application with php-fpm, and Elasticsearch.

Perform the following in the root project directory:

1. `npm install`
2. `npm run dev`
3. `cp .env.example .env`
3. `docker-compose up -d`
4. `docker-compose exec app composer install`
5. `docker-compose exec app php artisan key:generate`

## Import Woocommerce data
1. Edit .env WOOCOMMERCE_ variables to include your own
2. `docker-compose exec app php artisan woocommerce:import`

## Navigate to the home page
http://localhost

## Logs
Tail the docker logs: `docker-compose logs -f`

## Application versions
- php 7.2
- Laravel 5.8
- Elasticsearch 7.1

## Notable files added/modified
- app/Console/Commands/WoocommerceImport.php
- app/Http/Controllers/OrderController.php
- app/Http/Controllers/SearchController.php
- nginx/*
- resources/js/components/*
- resources/js/app.js
- resources/views/*
- routes/web.php
- Dockerfile
- docker-compose.yml

## Resources

Some code was copied and/or adapted from the following:

- https://www.digitalocean.com/community/tutorials/how-to-set-up-laravel-nginx-and-mysql-with-docker-compose
- https://vuejsdevelopers.com/2018/02/05/vue-laravel-crud/
