<?php

namespace App\Helpers;

class ApiFormatter {
    protected static $response = [ //static propertyw
        "status" => NULL,
        "message" => NULL,
        "data" => NULL,
    ];

    public static function sendResponse($status = NULL, $message = NULL, $data = [])
    {
        self::$response['status'] = $status;
        self::$response['message'] = $message;
        self::$response['data'] = $data;
        return response()->json(self::$response, self::$response['status']);
        //status : http status code (200, 400, 500)
        //message : desc http status  code ('succes', 'bad request', 'server error')
    }
}
?>