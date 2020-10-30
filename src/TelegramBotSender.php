<?php

namespace Instasaved;

class TelegramBotSender
{
    protected $apiUrl = 'https://api.telegram.org';

    protected $token;

    public function __construct($token)
    {
         $this->token = $token;
    }

    public function send($chatId, $msg) {
        $params = http_build_query(['chat_id' => $chatId, 'text' => $msg]);
        $url = sprintf("%s/bot%s/sendmessage?%s", $this->apiUrl, $this->token, $params);
        file_get_contents($url);
    }

}