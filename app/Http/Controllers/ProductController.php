<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductImportRequest;
use App\Jobs\ProcessProductsJob;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return $this->resolve('All Products',  Product::all());
    }

    public function show($id)
    {
        return $this->resolve('Product found',   Product::find($id));
    }

    public function store(Request $request)
    {
        return Product::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());

        return $product;
    }

    public function delete(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return 204;
    }

    public function import(ProductImportRequest $request)
    {
        if (!$request->hasFile('file')) {
            throw new Exception('CSV file is missing');
        }

        $file = $request->file('file');

        // Generate a file name with extension
        $fileName = 'products-'.time().'.'.$file->getClientOriginalExtension();

        // Save the file
        $file->storeAs(env('PRODUCT_IMPORT_DIR'), $fileName);

        ProcessProductsJob::dispatch($fileName);

        return $this->resolve('File saved');
    }
}
