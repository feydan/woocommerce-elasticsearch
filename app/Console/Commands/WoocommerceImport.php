<?php

namespace App\Console\Commands;

use Elasticsearch;
use Illuminate\Console\Command;
use Woocommerce;

class WoocommerceImport extends Command
{
    const INDEX_NAME = 'orders';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'woocommerce:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports data from the Woocommerce API into Elasticsearch';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->initIndex();

        $bar = $this->output->createProgressBar();

        $this->info('Indexing orders');
        foreach ($this->iterateOrders() as $ordersChunk) {
            $this->indexOrders($ordersChunk);
            $bar->advance(count($ordersChunk));
        }
        $bar->finish();

        $this->output->success('Finished import Woocommerce data.');
    }

    /**
     * Initialize the index
     */
    protected function initIndex()
    {
        // TODO We could use aliases to build a new index first and switch it over to maintain functionality
        // TODO while the reindex happens instead of deleting first
        try {
            Elasticsearch::connection()->indices()->delete(['index' => self::INDEX_NAME]);
            $this->info('Deleted previous index.');
        } catch (Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            $this->info('No index to delete, proceeding.');
        }

        $multiFieldMapping = [
            'type' => 'text',
            'fields' => [
                'raw' => [
                    'type' => 'keyword'
                ]
            ]
        ];

        $this->info('Creating index.');
        // Create the index with proper mappings
        Elasticsearch::connection()->indices()->create([
            'index' => self::INDEX_NAME,
            'body' => [
                'settings' => [
                    // TODO Change these settings on production to allow for high availability and ~50gb per shard
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                ],
                'mappings' => [
                    'dynamic' => 'strict',
                    'properties' => [
                        'id' => ['type' => 'keyword'],
                        'shipping_first_name' => $multiFieldMapping,
                        'shipping_last_name' => $multiFieldMapping,
                        'shipping_address_1' => $multiFieldMapping,
                        'shipping_city' => $multiFieldMapping,
                        'shipping_state' => $multiFieldMapping,
                        'shipping_postcode' => $multiFieldMapping,
                        'line_items' => [
                            'type' => 'nested',
                            'properties' => [
                                'name' => $multiFieldMapping,
                                'price' => ['type' => 'float'],
                                'quantity' => ['type' => 'integer'],
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * Iterates all Woocommerce orders and yields them in chunks
     *
     * This function automatically pages through all orders
     *
     * @return \Generator
     */
    protected function iterateOrders()
    {
        $data = [
            'page' => 1,
            'per_page' => 100
        ];

        while (!empty($orders = Woocommerce::get('orders', $data))) {
            yield $orders;

            $data['page']++;
        }
    }

    /**
     * Indexes the $orders into Elasticsearch
     *
     * @param array $orders
     */
    protected function indexOrders(array $orders)
    {
        $bulkParams = [];

        foreach ($orders as $order) {
            $bulkParams[] = [
                'index' => [
                    '_index' => self::INDEX_NAME,
                    '_id' => $order['id']
                ]
            ];
            $bulkParams[] = [
                'id' => $order['id'],
                'shipping_first_name' => $order['shipping']['first_name'],
                'shipping_last_name' => $order['shipping']['last_name'],
                'shipping_address_1' => $order['shipping']['address_1'],
                'shipping_city' => $order['shipping']['city'],
                'shipping_state' => $order['shipping']['state'],
                'shipping_postcode' => $order['shipping']['postcode'],
                'line_items' => array_map(
                    function ($lineItem) {
                        return [
                            'name' => $lineItem['name'],
                            'price' => $lineItem['total'],
                            'quantity' => $lineItem['quantity'],
                        ];
                    },
                    $order['line_items']
                ),
            ];
        }

        Elasticsearch::connection()->bulk([
            'index' => self::INDEX_NAME,
            'body' => $bulkParams,
        ]);
    }
}
