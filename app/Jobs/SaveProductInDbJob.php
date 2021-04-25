<?php


namespace App\Jobs;

use App\Contracts\ProductRepository;
use Illuminate\Support\Facades\Log;

class SaveProductInDbJob extends Job
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
            $productRepo->create($this->data);
        } catch (Exception $e) {
            Log::info(__CLASS__.' Saving product failed code-'.$this->data['code'].
                'message '.$e->getMessage());
            return;
        }
    }
}
