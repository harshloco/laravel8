<?php

namespace App\Jobs;

use App\Handlers\ImportHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessStocksJob extends Job
{

    /**
     * @var string
     */
    protected $filename;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ImportHandler $importHandler)
    {
        $local = Storage::disk('local');
        $path = env('STOCK_IMPORT_DIR').'/'.$this->filename;
        $fileExists = $local->exists($path);

        if ($fileExists) {
            try {
                $importHandler->import(
                    $path,
                    env('STOCK_IMPORT_DIR')
                );
            } catch (Exception $e) {
                throw $e;
            }
        } else {
            Log::info(__CLASS__." file doesn't exists ".$this->filename);
        }
    }
}
