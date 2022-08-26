<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrepareResponseController extends Controller
{
    public function simple_response($status,$display_message,$dev_message,$details,$statusCode){

        return response([
            "status" => $status,
            "display_message" => $display_message,
            "dev_message" => $dev_message,
            "details" =>$details,
        ],$statusCode);
    }
}
