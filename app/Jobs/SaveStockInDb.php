<?php


namespace App\Jobs;

use App\Contracts\ProductRepository;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveStockInDb extends Job
{
    public $tries = 1;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle(
        ProductRepository $productRepo
    ) {
        try {
            $productCode = $this->data['product_code'];
            //find the product
            $product = $productRepo->getByCode($productCode);

            if ($product) {
                $this->data =  $this->data + ['product_id' => $product->id];
                unset( $this->data['product_code']);

                DB::table('stocks')->upsert(
                    [
                        $this->data
                    ],
                    ['product_id', 'production_date'],
                    ['on_hand' => DB::raw('stocks.on_hand + '. $this->data['on_hand'])]
                );
            } else {
                Log::info(__CLASS__.' product not found '.$productCode);
            }
        } catch (Exception $e) {
            Log::info('Saving stock failed id-'.$productCode.
                'message '.$e->getMessage());
            return;
        }
    }
}
