<?php

namespace Instasaved;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log
{
    private static $logger;

    public static function __callStatic($method, $message) {
        self::getLogger()->{$method}(...$message);
    }

    private static function getLogger() {
        if (!isset(self::$logger)) {
            $handlers = [
                new StreamHandler('php://stdout'),
                new StreamHandler(getenv('MONOLOG_FILE')),
                new StreamHandler(getenv('MONOLOG_FILE') . '.alert', Logger::ALERT),
            ];
            $log = new Logger('Instasaved', $handlers);
            self::$logger = $log;
        }
        return self::$logger;
    }
}