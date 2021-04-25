<?php

namespace App\Jobs;

use App\Handlers\ImportHandler;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessProductsJob extends Job
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
     * @throws Exception
     */
    public function handle(ImportHandler $importHandler)
    {
        try {
            $local = Storage::disk('local');
            $path = env('PRODUCT_IMPORT_DIR').'/'.$this->filename;
            $fileExists = $local->exists($path);

            if ($fileExists) {
                try {
                    $importHandler->import(
                        $path,
                        env('PRODUCT_IMPORT_DIR')
                    );
                } catch (Exception $e) {
                    throw $e;
                }
            } else {
                Log::info(__CLASS__." file doesn't exists ".$this->filename);
                throw new Exception("file doesn't exists ".$this->filename);
            }
        } catch (Exception $e) {
            Log::warning('ProcessProductsJob failed attempt', [
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw new FailedJobException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
