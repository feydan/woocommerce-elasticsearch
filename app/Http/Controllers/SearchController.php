<?php

namespace App\Http\Controllers;

use Elasticsearch;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\ValidationException;

class SearchController extends BaseController
{
    use ValidatesRequests;

    /** @var array  */
    protected $matchFields = [
        'shipping_first_name',
        'shipping_last_name',
        'shipping_address_1',
        'shipping_city',
        'shipping_state',
        'shipping_postcode',
    ];

    /** @var array  */
    protected $lineItemMatchFields = [
        'line_items.name',
        'line_items.price',
        'line_items.quantity'
    ];

    /**
     * Searches with the given query request param text
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function search(Request $request)
    {
        $this->validate($request, [
            'query' => 'nullable|string'
        ]);

        $searchText = $request->get('query', '') ?? '';

        return JsonResponse::create($this->searchOrders($searchText));
    }

    /**
     * Searches the orders index using the given query string and returns results from the index
     *
     * @param string $searchText
     *
     * @return array
     */
    protected function searchOrders(string $searchText = '')
    {
        $lineItemQueries = [
            [
                'match' => [
                    'line_items.name' => $searchText
                ]
            ]
        ];

        // Elasticsearch throws an error if the query text can't be converted to a number
        // So don't include these fields unless the search text is numeric
        if (is_numeric($searchText)) {
            $lineItemQueries[] = [
                'match' => [
                    'line_items.price' => $searchText
                ]
            ];
            $lineItemQueries[] = [
                'match' => [
                    'line_items.quantity' => $searchText
                ]
            ];
        }

        $shouldQueries = [
            // Match on exact id
            [
                'term' => [
                    'id' => $searchText,
                ]
            ],
            // Match on match fields
            [
                'multi_match' => [
                    'query' => $searchText,
                    'fields' => $this->matchFields
                ]
            ],
            // Match on line item fields
            [
                'nested' => [
                    'path' => 'line_items',
                    'query' => [
                        'bool' => [
                            'should' => $lineItemQueries
                        ]
                    ]
                ]
            ]
        ];

        $params = [
            'index' => config('elasticsearch.defaultIndex'),
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => $shouldQueries
                    ]
                ],
            ],
            'size' => 20
        ];

        if ('' === $searchText) {
            $params['body'] = [
                'query' => [
                    'match_all' => (object) []
                ]
            ];
        }

        $response = Elasticsearch::connection()->search($params);

        $out = [];

        foreach ($response['hits']['hits'] as $hit) {
            $out[] = $hit['_source'];
        }

        return $out;
    }
}
