<?php
use Instasaved\Log;
use Instasaved\ServiceChecker;
use Instasaved\TelegramBotSender;

require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

try {
    $checker = new ServiceChecker();
    if ($checker->isDown()) {
        Log::alert('Service is down');
        $token = getenv('TELEGRAM_BOT_TOKEN');
        $chatId = getenv('TELEGRAM_CHAT_ID');
        (new TelegramBotSender($token))->send($chatId, 'Service is down')
            ? Log::alert('Message sent')
            : Log::emergency('Message was not send');
    } else {
        Log::info('Service is working');
    }
} catch (Exception $e) {
    Log::emergency($e->getMessage());
}