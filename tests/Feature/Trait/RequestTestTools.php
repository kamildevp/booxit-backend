<?php

namespace App\Tests\Feature\Trait;

use App\DataFixtures\Test\Global\VerifiedUserFixtures;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait RequestTestTools
{
    protected function jsonRequest(
        KernelBrowser $client,
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = []
    ) {
        $method = strtoupper($method);

        $headers = [
            'HTTP_ACCEPT' => 'application/json',
        ];

        if(!empty($authHeader = $client->getServerParameter('HTTP_AUTHORIZATION'))){
            $headers = array_merge($headers, ['HTTP_AUTHORIZATION' => $authHeader]);
        }

        if ($method !== 'GET') {
            $headers['CONTENT_TYPE'] = 'application/json';
        }

        $content = $method !== 'GET' ? json_encode($parameters) : null;
        $query = $method === 'GET' ? $parameters : [];

        return $client->request(
            $method,
            $uri,
            $query, 
            $files,
            array_merge($headers, $server),
            $content
        );
    }

    protected function fullLogin(KernelBrowser $client){
        $this->jsonRequest($client, 'POST', '/api/auth/login', [
            'email' => VerifiedUserFixtures::VERIFIED_USER_EMAIL,
            'password' => VerifiedUserFixtures::VERIFIED_USER_PASSWORD
        ]);
        $decodedResponse = $this->getJsonResponse($client);
        $accessToken = $decodedResponse['data']['access_token'];
        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $accessToken);

        return $decodedResponse['data'];
    }
}