<?php


namespace App\Handlers;

use App\Jobs\SaveProductInDbJob;
use App\Jobs\SaveStockInDb;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportHandler
{

    public function import(
        string $filePath,
        string $processName
    ) {

        try {
            $handle = fopen('./storage/app/'.$filePath, "r");
            // the loop its currently iterating over
            $lineNumber = 1;
            $emptyRows = 0;

            // Iterate over every line of the file
            while (($raw_string = fgets($handle)) !== false) {
                if ($lineNumber == 1) {
                    $lineNumber++;
                    continue;
                }
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

                if ($processName == env('PRODUCT_IMPORT_DIR')) {
                    if (isset($row[0]) && isset($row[1]) && isset($row[2])) {
                        $data = [
                            'code' => trim($row[0]),
                            'name' => trim($row[1]),
                            'description' => trim($row[2])
                        ];
                        SaveProductInDbJob::dispatch($data)->delay(now()->addSeconds(2));
                    }
                } elseif ($processName == env('STOCK_IMPORT_DIR')) {
                    if (isset($row[0]) && isset($row[1]) && isset($row[2])) {
                        $data = [
                            'product_code' => trim($row[0]),
                            'on_hand' => trim($row[1]),
                            'production_date' => Carbon::createFromFormat('d/m/Y',trim($row[2]))->startOfDay()
                        ];
                        SaveStockInDb::dispatch($data)->delay(now()->addSeconds(2));
                    }
                } else {
                    Log::info(__LINE__.' '.__FILE__.' no process to handle '.$processName);
                }
            }
            fclose($handle);
        } catch (Exception $e) {
            Log::warning('ImportHandler failed', [
                'error' => $e->getMessage(),
            ]);

            throw new FailedJobException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
