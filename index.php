<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

try {
    $checker = new Instasaved\ServiceChecker();
    if ($checker->isDown()) {
        $chatId = getenv('TELEGRAM_CHAT_ID');
        $token = getenv('TELEGRAM_BOT_TOKEN');
        $sender = new Instasaved\TelegramBotSender($token);
        $sender->send($chatId, 'Service is down');
    }
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
}
