<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleImportRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function import(VehicleImportRequest $request)
    {
        if (!$request->hasFile('file')) {
            throw new Exception('CSV file is missing');
        }

        $file = $request->file('file');

        // Generate a file name with extension
        $fileName = 'products-'.time().'.'.$file->getClientOriginalExtension();

        // Save the file
        $path = $file->storeAs('files', $fileName);

        // Read a CSV file
       // echo "Path : $path";
        return $path;
        $handle = fopen(($path), "r");

// Optionally, you can keep the number of the line where
// the loop its currently iterating over
        $lineNumber = 1;

// Iterate over every line of the file
        while (($raw_string = fgets($handle)) !== false) {
            // Parse the raw csv string: "1, a, b, c"
            $row = str_getcsv($raw_string, ',', '');

            // into an array: ['1', 'a', 'b', 'c']
            // And do what you need to do with every line
            var_dump($row);
            $data = [
                'code' => trim($row[0]),
                'name' => trim($row[1]),
                'description' => trim($row[2])
            ];
            Product::create($data);

            // Increase the current line
            $lineNumber++;
        }

        fclose($handle);

        return 200;

       // return 204;
    }
}
