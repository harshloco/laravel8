<?php

namespace App\Contracts;

use App\Models\Product;

interface ProductRepository
{
    /**
     * Creates the Product
     *
     * @param array $data
     *
     * @return Product
     */
    public function create(array $data);

    /**
     * List the Product by id
     *
     * @param int $id
     *
     * @return Product|null
     */
    public function getById(int $id);

    /**
     * List the Product by code
     *
     * @param string $code
     *
     * @return Product|null
     */
    public function getByCode(string $code);

    /**
     * List all the Product
     *
     * @param array $params
     * @return array
     */
    public function getAll(array $params = []);

    /**
     * List all the Product details
     *
     * @return array
     */
    public function getAllProductDetails(int $id);
}
