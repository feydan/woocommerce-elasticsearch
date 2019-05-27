# Woocommerce Elasticsearch
This project demonstrates live search using Elasticsearch with a Woocommerce api integration for fetching data and indexing it into Elasticsearch

## Requirements on host system
- Docker and docker-compose
- NPM

## Installation
Docker-compose is configured to install the dependencies necessary for this project

1. `docker-compose up -d` in the root directory
2. `npm install`
3. `npm run dev`

## Import Woocommerce data
`docker-compose exec app php artisan woocommerce:import`

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