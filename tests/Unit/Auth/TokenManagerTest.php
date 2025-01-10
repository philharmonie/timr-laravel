<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use PhilHarmonie\Timr\Auth\TokenManager;
use PhilHarmonie\Timr\Exceptions\TimrException;

it('returns the cached token if available', function () {
    Cache::shouldReceive('get')
        ->once()
        ->with('timr_access_token')
        ->andReturn('cached_token');

    $manager = new TokenManager('client_id', 'client_secret', 'http://example.com');

    $token = $manager->getToken();

    expect($token)->toBe('cached_token');
});

it('fetches a new token if not cached', function () {
    Cache::shouldReceive('get')
        ->once()
        ->with('timr_access_token')
        ->andReturn(null);

    Cache::shouldReceive('put')
        ->once()
        ->with('timr_access_token', 'fetched_token', 3500);

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with('http://example.com', [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => [
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'grant_type' => 'client_credentials',
                'scope' => 'openid timrclient',
            ],
        ])
        ->andReturn(new Response(200, [], json_encode(['access_token' => 'fetched_token'])));

    $manager = new TokenManager('client_id', 'client_secret', 'http://example.com', $httpClient);

    $token = $manager->getToken();

    expect($token)->toBe('fetched_token');
});

it('throws an exception if the token response is invalid', function () {
    Cache::shouldReceive('get')
        ->once()
        ->with('timr_access_token')
        ->andReturn(null);

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with('http://example.com', [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => [
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'grant_type' => 'client_credentials',
                'scope' => 'openid timrclient',
            ],
        ])
        ->andReturn(new Response(200, [], json_encode(['invalid_key' => 'no_token_here'])));

    $manager = new TokenManager('client_id', 'client_secret', 'http://example.com', $httpClient);

    $this->expectException(TimrException::class);
    $this->expectExceptionMessage('Token response is missing access_token.');

    $manager->getToken();
});

it('throws an exception if the token fetch fails', function () {
    Cache::shouldReceive('get')
        ->once()
        ->with('timr_access_token')
        ->andReturn(null);

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with('http://example.com', [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => [
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'grant_type' => 'client_credentials',
                'scope' => 'openid timrclient',
            ],
        ])
        ->andThrow(new Exception('HTTP request failed'));

    $manager = new TokenManager('client_id', 'client_secret', 'http://example.com', $httpClient);

    $this->expectException(TimrException::class);
    $this->expectExceptionMessage('Failed to fetch access token: HTTP request failed');

    $manager->getToken();
});
