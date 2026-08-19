<?php

declare(strict_types=1);

namespace PhilHarmonie\Timr\Http;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use PhilHarmonie\Timr\Contracts\TimrClientInterface;
use PhilHarmonie\Timr\Contracts\TokenProviderInterface;
use PhilHarmonie\Timr\Exceptions\TimrException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

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
        return $this->request('GET', $endpoint, ['query' => $params]);
    }

    public function patch(string $endpoint, array $data): array
    {
        return $this->request('PATCH', $endpoint, ['json' => $data]);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function request(string $method, string $endpoint, array $options): array
    {
        try {
            $accessToken = $this->tokenManager->getToken();

            $response = $this->client->request($method, $endpoint, array_merge_recursive($options, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Accept' => 'application/json, application/problem+json',
                ],
            ]));

            $responseData = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($responseData)) {
                throw new TimrException('Expected JSON response to be an array, got invalid type.');
            }

            return $responseData;
        } catch (BadResponseException $e) {
            throw $this->problemException($e);
        } catch (Exception $e) {
            throw new TimrException("Timr API request failed: {$e->getMessage()}", previous: $e);
        }
    }

    /**
     * Turn an error response into a TimrException, carrying the RFC 9457
     * problem details the API returns since v1 when they are present.
     */
    private function problemException(BadResponseException $e): TimrException
    {
        $response = $e->getResponse();
        $problem = $this->decodeProblem($response);

        if ($problem === null) {
            return new TimrException("Timr API request failed: {$e->getMessage()}", statusCode: $response->getStatusCode(), previous: $e);
        }

        return TimrException::fromProblemDetail($problem, $response->getStatusCode(), $e);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeProblem(ResponseInterface $response): ?array
    {
        try {
            $body = (string) $response->getBody();
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }
}
