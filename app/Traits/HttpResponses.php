<?php

namespace App\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

trait HttpResponses {

    // public static function sendError($message, $errors=[], $code=401)
    // {
    //     $response = ['success' => false, 'message'=> $message];

    //     if(!empty($errors)){
    //         $response['data'] = $errors;
    //     }
    //     throw new HttpResponseException(response()->json($response,$code));
    // }

    protected function success($data, string $message = null, int $code = 200)
    {
        return response()->json([
            'status' => 'Request was successful.',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function error($data, string $message = null, int $code)
    {
        return response()->json([
            'status' => 'An error has occurred...',
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
