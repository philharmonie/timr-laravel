<?php

declare(strict_types=1);

use PhilHarmonie\Timr\Auth\TokenManager;
use PhilHarmonie\Timr\Contracts\ProjectTimeRepositoryInterface;
use PhilHarmonie\Timr\Contracts\TimrClientInterface;
use PhilHarmonie\Timr\Http\TimrClient;
use PhilHarmonie\Timr\Service\TimrService;
use PhilHarmonie\Timr\ServiceProvider;
use RuntimeException;

beforeEach(function () {
    $this->provider = new ServiceProvider($this->app);

    // Mock the config helper to return null by default
    config(['timr' => null]);
});

test('register throws exception with missing base_url', function () {
    config([
        'timr.token' => 'test-token',
        'timr.base_url' => null,
    ]);

    expect(fn () => $this->provider->register())
        ->toThrow(RuntimeException::class, 'Timr API configuration is missing or invalid');
});

test('register throws exception with missing token', function () {
    config([
        'timr.base_url' => 'http://api.example.com',
        'timr.token' => null,
    ]);

    expect(fn () => $this->provider->register())
        ->toThrow(RuntimeException::class, 'Timr API configuration is missing or invalid');
});

test('register throws exception with empty base_url', function () {
    config([
        'timr.base_url' => '',
        'timr.token' => 'test-token',
    ]);

    expect(fn () => $this->provider->register())
        ->toThrow(RuntimeException::class, 'Timr API configuration is missing or invalid');
});

test('register throws exception with empty token', function () {
    config([
        'timr.base_url' => 'http://api.example.com',
        'timr.token' => '',
    ]);

    expect(fn () => $this->provider->register())
        ->toThrow(RuntimeException::class, 'Timr API configuration is missing or invalid');
});

test('register binds interfaces correctly', function () {
    config([
        'timr.base_url' => 'http://api.example.com',
        'timr.token_url' => 'http://api.example.com/token',
        'timr.client_id' => 'test-client-id',
        'timr.client_secret' => 'test-client-secret',
    ]);

    $this->provider->register();

    // Test TokenManager binding
    $tokenManager = $this->app->make(TokenManager::class);
    expect($tokenManager)->toBeInstanceOf(TokenManager::class);

    // Test TimrClientInterface binding
    $timrClient = $this->app->make(TimrClientInterface::class);
    expect($timrClient)->toBeInstanceOf(TimrClient::class);

    // Test ProjectTimeRepositoryInterface binding
    $repository = $this->app->make(ProjectTimeRepositoryInterface::class);
    expect($repository)->toBeInstanceOf(ProjectTimeRepositoryInterface::class);

    // Test 'timr' binding
    $timrService = $this->app->make('timr');
    expect($timrService)->toBeInstanceOf(TimrService::class);
});
