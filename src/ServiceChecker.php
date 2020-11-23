<?php

namespace Instasaved;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Http\Message\ResponseInterface;

class ServiceChecker
{
    const SERVICE_BASE_URL = 'https://test.instasaved.net';
    const SERVICE_AJAX_URL = 'https://test.instasaved.net/ajax-instasaver';
    const INSTAGRAM_USER_URL = 'https://instagram.com/jlo';

    protected Client $client;
    protected CookieJar $cookies;

    public function __construct() {
        $this->cookies = new CookieJar();
        $this->client = new Client([
            'base_uri' => self::SERVICE_BASE_URL,
            'cookies' => $this->cookies,
        ]);
    }

    public function isDown() {
        $options = $this->prepareRequestOptions($this->client->get(self::SERVICE_BASE_URL));
        $json = $this->checkService($options);
        $result = ($json->type == 'userNotFound');
        if (!$result) {
            Log::debug('JSON', [$json->type]);
        }
        return $result;
    }

    /**
     * @param array $options
     * @return \stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function checkService(array $options): \stdClass
    {
        $data = $this->client->request('POST', self::SERVICE_AJAX_URL, $options)
            ->getBody()
            ->getContents();
        return \json_decode($data);
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function prepareRequestOptions(ResponseInterface $response): array
    {
        $x_xsrf_token = $this->fetchXsrfTokenFromCookie();
        $token = $this->fetchFormToken($response);

        $options = [
            'headers'   => [ 'X-XSRF-TOKEN' => $x_xsrf_token ],
            'json'      => [ 'token' => $token, 'type' => 'story', 'username' => self::INSTAGRAM_USER_URL ],
        ];

        return $options;
    }

    /**
     * @return string|null
     */
    protected function fetchXsrfTokenFromCookie()
    {
        return str_replace('%3D', '=', $this->cookies->getCookieByName('XSRF-TOKEN')->getValue());
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param $matches
     * @return string
     */
    protected function fetchFormToken(\Psr\Http\Message\ResponseInterface $response): string
    {
        $content = $response->getBody()->getContents();
        preg_match('/<input .* name="token" .* value="(.*)">/', $content, $matches);
        $token = $matches[1];
        return $token;
    }
}