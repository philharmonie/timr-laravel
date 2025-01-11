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

it('throws exception with invalid configuration', function (array $config) {
    config($config);

    // Register services
    $this->provider->register();

    // Try to resolve the service which should trigger validation
    expect(fn () => $this->app->make(TokenManager::class))
        ->toThrow(RuntimeException::class, 'Timr API configuration is missing or invalid');
})->with([
    'missing base_url' => [[
        'timr.token_url' => 'test-token',
        'timr.base_url' => null,
        'timr.client_id' => 'test-client-id',
        'timr.client_secret' => 'test-client-secret',
    ]],
    'missing token_url' => [[
        'timr.base_url' => 'http://api.example.com',
        'timr.token_url' => null,
        'timr.client_id' => 'test-client-id',
        'timr.client_secret' => 'test-client-secret',
    ]],
    'empty base_url' => [[
        'timr.base_url' => '',
        'timr.token_url' => 'test-token',
        'timr.client_id' => 'test-client-id',
        'timr.client_secret' => 'test-client-secret',
    ]],
    'empty token_url' => [[
        'timr.base_url' => 'http://api.example.com',
        'timr.token_url' => '',
        'timr.client_id' => 'test-client-id',
        'timr.client_secret' => 'test-client-secret',
    ]],
]);

it('binds interfaces correctly', function () {
    config([
        'timr.base_url' => 'http://api.example.com',
        'timr.token_url' => 'http://api.example.com/token',
        'timr.client_id' => 'test-client-id',
        'timr.client_secret' => 'test-client-secret',
    ]);

    $this->provider->register();

    expect($this->app->make(TokenManager::class))
        ->toBeInstanceOf(TokenManager::class)
        ->and($this->app->make(TimrClientInterface::class))
        ->toBeInstanceOf(TimrClient::class)
        ->and($this->app->make(ProjectTimeRepositoryInterface::class))
        ->toBeInstanceOf(ProjectTimeRepositoryInterface::class)
        ->and($this->app->make('timr'))
        ->toBeInstanceOf(TimrService::class);
});
