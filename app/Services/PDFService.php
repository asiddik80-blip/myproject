<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PDFService
{
    protected $client;
    protected $clientId;
    protected $clientSecret;

    public function __construct()
    {
        $this->client = new Client();
        $this->clientId = env('ADOBE_CLIENT_ID');
        $this->clientSecret = env('ADOBE_CLIENT_SECRET');
    }

    public function getAccessToken()
    {
        try {
            $response = $this->client->post('https://ims-na1.adobelogin.com/ims/token/v2', [
                'form_params' => [
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type'    => 'client_credentials',
                    'scope'         => 'DCAPI', // Pastikan sesuai dengan yang berhasil di PowerShell
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['access_token'] ?? null;
        } catch (RequestException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
