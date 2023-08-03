<?php

namespace App\Intergations\MeestExpress;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MeestExpress
{
    const TOKEN_CACHE_KEY_NAME = 'meest-express-auth-token';
    private Client $client;

    private string $username;

    private string $password;

    private string $token;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;

        $this->client = new Client();

        $this->token = $this->getToken();
    }

    public function getCities(string $query = ''): Collection
    {
        try {
            $response = $this->client->post('https://api.meest.com/v3.0/openAPI/citySearch', [
                'body' => json_encode([
                    'filters' => [
                        'cityDescr' => $query,
                    ],
                ]),
                'headers' => [
                    'token' => $this->token,
                ]
            ]);

            return collect(json_decode($response->getBody()->getContents(), true)['result']);
        } catch (\Exception $exception) {
            Log::error('Error during getting meest express cities: ' . $exception->getMessage());
            return collect();
        }
    }

    public function getCityByRef(string $ref = ''): Collection
    {
        try {
            $response = $this->client->post('https://api.meest.com/v3.0/openAPI/citySearch', [
                'body' => json_encode([
                    'filters' => [
                        'cityID' => $ref,
                    ],
                ]),
                'headers' => [
                    'token' => $this->token,
                ]
            ]);

            return collect(json_decode($response->getBody()->getContents(), true)['result']);
        } catch (\Exception $exception) {
            Log::error('Error during getting meest express cities: ' . $exception->getMessage());
            return collect();
        }
    }

    public function getDepartments(string $cityRef): Collection
    {
        try {
            $response = $this->client->post('https://api.meest.com/v3.0/openAPI/branchSearch', [
                'body' => json_encode([
                    'filters' => [
                        'cityID' => $cityRef,
                    ],
                ]),
                'headers' => [
                    'token' => $this->token,
                ]
            ]);

            return collect(json_decode($response->getBody()->getContents(), true)['result']);
        } catch (\Exception $exception) {
            Log::error('Error during getting meest express cities: ' . $exception->getMessage());
            return collect();
        }
    }

    public function getDepartmentByRef(string $departmentRef): Collection
    {
        try {
            $response = $this->client->post('https://api.meest.com/v3.0/openAPI/branchSearch', [
                'body' => json_encode([
                    'filters' => [
                        'branchID' => $departmentRef,
                    ],
                ]),
                'headers' => [
                    'token' => $this->token,
                ]
            ]);
            return collect(json_decode($response->getBody()->getContents(), true)['result']);
        } catch (\Exception $exception) {
            Log::error('Error during getting meest express cities: ' . $exception->getMessage());
            return collect();
        }
    }

    private function storeToken(): ?string
    {
        try {
            $response = $this->client->post( 'https://api.meest.com/v3.0/openAPI/auth', [
                'body' => json_encode([
                    'username' => $this->username,
                    'password' => $this->password,
                ]),
                'headers' => [
                    'Content-Type' => 'application/json',
                ]

            ]);

            $token = json_decode($response->getBody()->getContents(), true)['result']['token'];

            Cache::set(self::TOKEN_CACHE_KEY_NAME, $token, now()->addMinutes(10));

            return $token;
        } catch (\Exception $exception) {
            Log::error('Error during generating meest express auth token: ' . $exception->getMessage());
        }

        return null;
    }

    private function getToken(): string
    {
        $tokenFromCache = Cache::get(self::TOKEN_CACHE_KEY_NAME);

        if (!$tokenFromCache) {
            $tokenFromCache = $this->storeToken();
        }

        return $tokenFromCache;
    }
}
