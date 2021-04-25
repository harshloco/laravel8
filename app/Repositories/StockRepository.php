<?php


namespace App\Repositories;

use App\Contracts\StockRepository as StockRepositoryContract;
use App\Models\Stock;

class StockRepository implements StockRepositoryContract
{

    public function create(array $data)
    {
        $stock = new Stock();
        $stock->fill($data)->save();
        return $stock;
    }

    public function getById(int $id)
    {
        return Stock::find($id);
    }

    public function getAll()
    {
        return Stock::all();
    }
}
