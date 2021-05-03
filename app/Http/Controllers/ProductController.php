<?php

namespace App\Http\Controllers;

use App\Contracts\ProductRepository;
use App\Contracts\StockRepository;
use App\Http\Requests\ProductImportRequest;
use App\Http\Requests\ProductStockStoreRequest;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Requests\ViewAllProductsRequest;
use App\Jobs\ProcessProductsJob;
use App\Models\Product;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * @var ProductRepository
     */
    protected $productRepo;
    /**
     * @var StockRepository
     */
    protected $stockRepo;

    public function __construct(ProductRepository $productRepo, StockRepository $stockRepo) {
        $this->productRepo = $productRepo;
        $this->stockRepo = $stockRepo;
    }

    /**
     * Comment - for filtering - package like czim/laravel-filter can be implemented
     *
     * @param ViewAllProductsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(ViewAllProductsRequest $request)
    {
        $params = $request->only('filters', 'sortBy', 'sortType', 'perPage', 'pagination', 'stock');

        $products = $this->productRepo->getAll($params);
        return $this->resolve('All Products',$products);
    }

    /**
     * @param ViewAllProductsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showDetails(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $params = $request->only( 'stock');

        $products = $this->productRepo->getAllProductDetails($id, $params['stock'] ?? false);
        return $this->resolve('All Product Details', $products);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return $this->resolve('Product found', Product::findOrFail($id));
    }

    /**
     * @param ProductStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductStoreRequest $request)
    {
        try {
            $payload = $request->only('code', 'name', 'description');
            $product = $this->productRepo->create($payload);
        } catch (Exception $e) {
            return $this->reject($e->getMessage());
        }
        return $this->resolve('Product created successfully', $product);
    }

    /**
     * @param ProductStockStoreRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeStock(ProductStockStoreRequest $request, $id)
    {
        try {
            $payload = $request->only('onHand', 'taken', 'productionDate');
            $data = [
                'on_hand' => $payload['onHand'] ?? 0,
                'taken' => ($payload['taken']) ?? 0,
                'production_date' => ($payload['productionDate']) ?
                    Carbon::createFromFormat('d/m/Y', $payload['productionDate'])->startOfDay() :
                    Carbon::now(),
            ];

            $stock = Stock::updateOrCreate
            (
                ['product_id' => $id],
                $data
            );
        } catch (Exception $e) {
            return $this->reject($e->getMessage());
        }
        return $this->resolve('Stock created', $stock);
    }

    /**
     * @param ProductUpdateRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ProductUpdateRequest $request, $id)
    {
        try {
            $payload = $request->only('code', 'name', 'description');
            $product = Product::findOrFail($id);
            $product->update($payload);
        } catch (Exception $e) {
            return $this->reject($e->getMessage());
        }
        return $this->resolve('Product updated successfully', $product);
    }

    /**
     * Comment - To improve observer can be implemented to delete the stocks related to this product
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
        } catch (Exception $e) {
            return $this->reject($e->getMessage());
        }
        return $this->resolve('Product deleted successfully');
    }

    /**
     * @param ProductImportRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
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

        ProcessProductsJob::dispatch($fileName)->delay(now()->addSeconds(2));

        return $this->resolve('File saved');
    }
}
