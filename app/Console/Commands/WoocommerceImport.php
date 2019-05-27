<?php

namespace App\Console\Commands;

use App\Transforms\OrderTransform;
use Elasticsearch;
use Illuminate\Console\Command;
use Woocommerce;

class WoocommerceImport extends Command
{
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

    /** @var */
    protected $indexName;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->indexName = config('elasticsearch.defaultIndex');
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
            Elasticsearch::connection()->indices()->delete(['index' => $this->indexName]);
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
            'index' => $this->indexName,
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
                    '_index' => $this->indexName,
                    '_id' => $order['id']
                ]
            ];
            $bulkParams[] = OrderTransform::transformToElasticsearch($order);
        }

        Elasticsearch::connection()->bulk([
            'index' => $this->indexName,
            'body' => $bulkParams,
        ]);
    }
}
