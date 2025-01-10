<?php

declare(strict_types=1);

namespace PhilHarmonie\Timr\Http;

use Exception;
use GuzzleHttp\Client;
use PhilHarmonie\Timr\Contracts\TimrClientInterface;
use PhilHarmonie\Timr\Contracts\TokenProviderInterface;
use PhilHarmonie\Timr\Exceptions\TimrException;

final readonly class TimrClient implements TimrClientInterface
{
    private Client $client;

    public function __construct(
        private TokenProviderInterface $tokenManager,
        string $baseUrl,
        ?Client $client = null
    ) {
        $this->client = $client ?? new Client(['base_uri' => $baseUrl]);
    }

    public function get(string $endpoint, array $params = []): array
    {
        try {
            $accessToken = $this->tokenManager->getToken();

            $response = $this->client->get($endpoint, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Accept' => 'application/json',
                ],
                'query' => $params,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($responseData)) {
                throw new TimrException('Expected JSON response to be an array, got invalid type.');
            }

            return $responseData;
        } catch (Exception $e) {
            throw new TimrException("Timr API request failed: {$e->getMessage()}");
        }
    }
}
