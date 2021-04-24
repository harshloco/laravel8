<?php

namespace App\Jobs;

use App\Handlers\Vehicle\ImportHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
    public function handle()
    {
        $local = Storage::disk('local');
        $path = '/'.env('PRODUCT_IMPORT_DIR').'/'.$this->filename;
        $fileExists = $local->exists($path);

        if ($fileExists) {
            $handle = fopen('./storage/app/files/'.$this->filename, "r");
            // Optionally, you can keep the number of the line where
            // the loop its currently iterating over
            $lineNumber = 1;
            $emptyRows = 0;

            // Iterate over every line of the file
            while (($raw_string = fgets($handle)) !== false) {

                if($lineNumber == 1){ $lineNumber++; continue; }
                // Increase the current line
                $lineNumber++;

                // Parse the raw csv string: "1, a, b, c"
                // into an array: ['1', 'a', 'b', 'c']
                $row = str_getcsv($raw_string, ',', '');

                if (strlen(implode(' ', (str_replace(',', '', $row)))) <= 0) {
                    //sanity check, if there are more than 10 empty rows in a sequence
                    //stop reading the next rows
                    if ($emptyRows == 10) {
                        break;
                    }
                    // this is empty row. skip to next row
                    $emptyRows++;
                    continue;

                }


                if(isset($row[0]) && isset($row[1]) && isset($row[2])) {
                    $data = [
                        'code' => trim($row[0]),
                        'name' => trim($row[1]),
                        'description' => trim($row[2])
                    ];
                    SaveProductInDbJob::dispatch($data);
                }
            }
            fclose($handle);
        } else {
            Log::info("file doesn't exists ".$this->filename);
        }
    }
}
