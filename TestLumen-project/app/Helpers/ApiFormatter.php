<?php 

namespace App\Helpers;
//namespace  : menentukan lokassi folder dari file ini

// nama class = nama file
class ApiFormatter {
    //variabel struktur data yang akan ditampilkan di response postman
    protected static $response =[
        "status" => NULL,
        "message" => NULL,
        "data" => NULL,
    ];

    public static function sendResponse($status = NULL, $message = NULL, $data = []){
        self::$response['status'] = $status;
        self::$response['message'] = $message;
        self::$response['data'] = $data;
        return response()->json(self::$response,self::$response['status']);
    }
}

?>