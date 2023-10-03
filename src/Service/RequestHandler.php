<?php

namespace App\Service;

use mysql_xdevapi\Exception;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RequestHandler
{
    const NASA_API_URL = "https://nasaapi.pl/api/";
    private string $token;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly CacheInterface      $cache,
        private readonly LoggerInterface     $logger,
    )
    {
    }


    public function nasaApiRequest(string $url): string|ResponseInterface
    {
        $this->setToken();

        try {
            $response = $this->client->request('POST', self::NASA_API_URL . $url, [
                'auth_bearer' => $this->token,
            ]);
            
            return $response;
        } catch (TransportExceptionInterface $e) {
            $e->getMessage();
        }
    }

    private function getNasaApiToken()
    {
        $body = json_encode([ 'username' => $GLOBALS['_ENV']['NASA_API_LOGIN'],
            'password' => $GLOBALS['_ENV']['NASA_API_PASSWORD']]);

        try {
           $response = $this->client->request('POST', self::NASA_API_URL . 'login_check', [
               'body' => $body,
               'headers' => [
                   'Content-Type' => 'application/json'
               ]
           ]);
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }

        try {
            $content = $response->getContent();
            $decodedContent = json_decode($content);
            return $decodedContent->token;

        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    private function setToken(): void
    {
        try {
            $this->token = $this->cache->get('jwt_token', function (ItemInterface $item) {
                $item->expiresAfter(3600);
                return $this->getNasaApiToken();
            });
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}