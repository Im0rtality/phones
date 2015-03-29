<?php

namespace Phones\PhoneBundle\Services;

use Guzzle\Http\Client;
use GuzzleHttp\Message\Response;

class Downloader
{
    /** @var  Client */
    protected $client;

    /** @var \GuzzleHttp\Client  */
    protected $guzzlehttpClient = null;

    /**
     * @param Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @param string $link
     * @return bool|string
     */
    public function getContent($link)
    {
        $response     = null;
        $responseBody = null;

        try {
            $request = $this->client->get($link);
            $response = $request->send();

            if ($response && $response->getStatusCode() == 200) {
                $responseBody = (string)$response->getBody();
            }
        } catch (\Exception $e) {
            try {
                //try with another guzzleClient
                if ($this->guzzlehttpClient == null) {
                    $this->guzzlehttpClient = new \GuzzleHttp\Client();

                }
                $response = $this->guzzlehttpClient->get($link);
                /** @var Response $response */
                if ($response && $response->getStatusCode() == 200) {
                    $responseBody = (string)$response->getBody();
                }
            } catch (\Exception $e2) {
            }
        }


        return $responseBody;
    }
}
