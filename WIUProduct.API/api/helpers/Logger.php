<?php

namespace helpers;

class Logger
{
    public static function log($error)
    {
        $log_filename = __DIR__ . '/log';
        if(!file_exists($log_filename)) {
            mkdir($log_filename, 0777, true);
        }
        $log_file_data = $log_filename.'/log_'.date('d-M-Y').'.log';
        file_put_contents($log_file_data, $error."\n", FILE_APPEND);
    }
}