<?php

namespace Instasaved;

use \GuzzleHttp\Client;
use \GuzzleHttp\Cookie\CookieJar;

class ServiceChecker
{
    protected Client $client;
    protected CookieJar $cookies;

    public function __construct() {
        $this->cookies = new CookieJar();
        $this->client = new Client([
            'base_uri' => 'https://test.instasaved.net',
            'cookies' => $this->cookies,
        ]);
    }

    public function isDown() {
        $options = $this->prepareRequestOptions();
        $json = $this->checkService($options);
        if ($json->type == 'userNotFound') {
            return true;
        }
        Log::debug('JSON', [$json->type]);
        return false;
    }

    /**
     * @param array $options
     * @return \stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function checkService(array $options): \stdClass
    {
        $data = $this->client->request('POST', 'https://test.instasaved.net/ajax-instasaver', $options)
            ->getBody()
            ->getContents();
        return \json_decode($data);
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function prepareRequestOptions(): array
    {
        $options = [];

        $response = $this->client->get('https://test.instasaved.net');
        $x_xsrf_token = $this->fetchXsrfTokenFromCookie();
        $token = $this->fetchFormToken($response);

        $options['headers'] = ['X-XSRF-TOKEN' => $x_xsrf_token];
        $options['json'] = ['token' => $token, 'type' => 'story', 'username' => 'https://instagram.com/jlo'];

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