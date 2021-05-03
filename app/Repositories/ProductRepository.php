<?php

namespace App\Repositories;

use App\Contracts\ProductRepository as ProductRepositoryContract;
use App\Models\Product;

class ProductRepository implements ProductRepositoryContract
{

    public function create(array $data)
    {
        $product = new Product();
        $product->fill($data)->save();
        return $product;
    }

    public function getById(int $id)
    {
        return Product::find($id);
    }

    public function getByCode(string $code)
    {
        return Product::whereCode($code)->first();
    }
    public function getAll(array $params = [])
    {
        $baseQuery =  Product::select('id','code');

        $baseQuery = $this->add_filters($baseQuery, $params);
        $baseQuery = $this->add_sort($baseQuery, $params);
        if (isset($params['stock']) && $params['stock'] == 'true') {
            $baseQuery = $baseQuery->with('stocks');
            unset($params['stock']);
        }
        return $this->with_pagination($baseQuery, $params);
    }

    public function getAllProductDetails(int $id, bool $stocks = false)
    {
        $product =  Product::whereId($id)->first();
        if($stocks){
            $product= Product::find($id)->stocks()->get();
        }
        return $product;
    }

    public function add_filters($baseQuery, $params)
    {
        $query = $baseQuery;
        if (in_array("filters", $params) || array_key_exists("filters", $params)) {
            $filters = $params["filters"];
            foreach ($filters as $filter) {
                $filter = json_decode($filter, true);
                if (isset($filter["type"]) && $filter["type"] != "whereIn") {
                    $query = $query->where($filter["field"], $filter["type"], $filter["value"]);
                } else {
                    $query = $query->whereIn($filter["field"], $filter["values"]);
                }
            }
        }
        return $query;
    }

    public function add_sort($baseQuery, $params)
    {
        $field = $params['sortBy'] ?? false;
        $order = $params['sortType'] ?? 'asc';
        if ($field) {
            return $baseQuery->orderBy($field, $order);
        };
        return $baseQuery;
    }

    public function with_pagination($baseQuery, $params)
    {
        if (isset($params["pagination"]) && $params["pagination"]) {
            if (isset($params["perPage"])) {
                return $baseQuery->paginate($params["perPage"]);
            }
            return $baseQuery->paginate(10);
        }
        return $baseQuery->paginate(10);
    }
}
