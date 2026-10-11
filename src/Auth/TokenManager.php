<?php

declare(strict_types=1);

namespace PhilHarmonie\Timr\Auth;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use PhilHarmonie\Timr\Contracts\TokenProviderInterface;
use PhilHarmonie\Timr\Exceptions\TimrException;

final readonly class TokenManager implements TokenProviderInterface
{
    private const CACHE_KEY = 'timr_access_token';

    private const CACHE_TTL = 3500; // Cache 3500 Sekunden (just before 1h)

    public function __construct(private string $clientId, private string $clientSecret, private string $tokenUrl, private Client $httpClient = new Client(['timeout' => 10])) {}

    public function getToken(): string
    {
        /** @var string */
        $cachedToken = Cache::get($this->cacheKey());
        if ($cachedToken) {
            return $cachedToken;
        }

        return $this->fetchToken();
    }

    /**
     * Scope the cached token to the credentials, so several TokenManager
     * instances (e.g. one per tenant) never share an access token.
     */
    public function cacheKey(): string
    {
        return self::CACHE_KEY.':'.hash('sha256', $this->tokenUrl.'|'.$this->clientId);
    }

    private function fetchToken(): string
    {
        try {
            $response = $this->httpClient->post($this->tokenUrl, [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'client_credentials',
                    'scope' => 'openid timrclient',
                ],
            ]);

            /** @var array<int, string> */
            $responseData = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            if (! isset($responseData['access_token'])) {
                throw new TimrException('Token response is missing access_token.');
            }

            $token = $responseData['access_token'];

            Cache::put($this->cacheKey(), $token, self::CACHE_TTL);

            return $token;
        } catch (Exception $e) {
            throw new TimrException('Failed to fetch access token: '.$e->getMessage());
        }
    }
}
