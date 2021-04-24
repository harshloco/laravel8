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

    public function getAll()
    {
        return Product::all();
    }
}
