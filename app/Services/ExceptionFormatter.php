<?php
namespace App\Services;

class ExceptionFormatter {
    
    static public function formatException($e) {
        $error = 'message: '. $e->getMessage()."\n";
        $error = $error.'code is:' . $e->getCode() . "\n";
        $error = $error.'file is:' . $e->getFile() . "\n";
        $error = $error.'line is:' . $e->getLine() . "\n";
        $error = $error.'trace is:' . $e->getTraceAsString() . "\n";
        return $error;
    }
    
    static public function formatStripeException($e) {
        $body = $e->getJsonBody();
        $err  = $body['error'];
        $error = 'Status is:' . $e->getHttpStatus() . "\n";
        $error = 'Type is:' . array_key_exists('type', $err) ? $err['type'] . "\n" : " \n";
        $error = 'Code is:' . array_key_exists('code', $err) ? err['code'] . "\n" : " \n";
        $error = 'Param is:' . array_key_exists('param', $err) ? $err['param'] . "\n" : " \n";
        $error = 'Message is:' . array_key_exists('message', $err) ? $err['message'] . "\n" : " \n";
        return $error;
    }
}