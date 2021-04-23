<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Lang;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Sends Success JSON Response
     *
     * @param string $message
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function resolve($message, $data = [])
    {
        if (!$data) {
            $data = [];
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Sends Reject JSON Reponse
     *
     * @param string $error
     * @param int    $responseCode
     * @param array  $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(string $error, int $responseCode = 200, array $data = [])
    {
        // Remove id(s) from the end of the message
        if (strpos($error, 'No query results for model') === 0) {
            $error = preg_replace('/ [, 0-9]+$/', '.', $error);
        }

        return response()->json([
            'success' => false,
            'message' => Lang::has('exceptions.' . $error) ? Lang::trans('exceptions.' . $error) : $error,
            'data' => $data
        ], $responseCode);
    }
}
