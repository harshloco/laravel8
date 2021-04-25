<?php

namespace App\Contracts;

use App\Models\Stock;

interface StockRepository
{
    /**
     * Creates the Stock
     *
     * @param array $data
     *
     * @return Stock
     */
    public function create(array $data);

    /**
     * List the Stock by id
     *
     * @param int $id
     *
     * @return Stock|null
     */
    public function getById(int $id);

    /**
     * List all the Stock
     *
     * @param void
     *
     * @return array
     */
    public function getAll();
}
