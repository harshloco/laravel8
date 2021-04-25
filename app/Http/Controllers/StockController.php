<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockImportRequest;
use App\Jobs\ProcessStocksJob;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        return $this->resolve('All Stocks', Stock::all());
    }

    public function show($id)
    {
        return $this->resolve('Stock found', Stock::find($id));
    }

    public function store(Request $request)
    {
        return Stock::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $stock = Stock::findOrFail($id);
        $stock->update($request->all());

        return $stock;
    }

    public function delete(Request $request, $id)
    {
        $stock = Stock::findOrFail($id);
        $stock->delete();

        return 204;
    }

    public function import(StockImportRequest $request)
    {
        if (!$request->hasFile('file')) {
            throw new Exception('CSV file is missing');
        }

        $file = $request->file('file');

        // Generate a file name with extension
        $fileName = 'stocks-'.time().'.'.$file->getClientOriginalExtension();

        // Save the file
        $file->storeAs(env('STOCK_IMPORT_DIR'), $fileName);

        ProcessStocksJob::dispatch($fileName)->delay(2);

        return $this->resolve('File saved');
    }
}
