<?php

namespace App\Helpers;

use Carbon\Carbon;

class EncoderApiAuth{

    public static function generateApiToken() {
        
        $newYorkTime = Carbon::now('America/New_York');
        $timestamp = $newYorkTime->timestamp;
        
        $data = hash('sha256', $timestamp);
        
        $time_key = substr($data, 0, 16);

        $privateKey = openssl_pkey_get_private(file_get_contents(storage_path(env('ENCODER_API_KEY_PATH'))));

        openssl_sign($time_key, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);

    }
}