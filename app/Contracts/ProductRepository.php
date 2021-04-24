<?php

namespace App\Contracts;

use App\Models\Product;
use Illuminate\Support\Collection;

interface ProductRepository
{
    /**
     * Creates the Account
     *
     * @param array $data
     *
     * @return Product
     */
    public function create(array $data);

    /**
     * List the Account by id
     *
     * @param int $id
     *
     * @return Product|null
     */
    public function getById(int $id);

    /**
     * List all the Accounts
     *
     * @param void
     *
     * @return array
     */
    public function getAll();


}
