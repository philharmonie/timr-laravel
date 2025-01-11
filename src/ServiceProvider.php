<?php

declare(strict_types=1);

namespace PhilHarmonie\Timr;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use PhilHarmonie\Timr\Auth\TokenManager;
use PhilHarmonie\Timr\Contracts\ProjectTimeRepositoryInterface;
use PhilHarmonie\Timr\Contracts\TimrClientInterface;
use PhilHarmonie\Timr\Http\TimrClient;
use PhilHarmonie\Timr\Repositories\ProjectTimeRepository;
use PhilHarmonie\Timr\Service\TimrService;
use RuntimeException;

final class ServiceProvider extends BaseServiceProvider
{
    private string $baseUrl;

    private string $tokenUrl;

    private string $clientId;

    private string $clientSecret;

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/timr.php',
            'timr'
        );

        $this->registerLazyServices();
    }

    private function registerLazyServices(): void
    {
        $this->app->singleton(TokenManager::class, function (): TokenManager {
            $this->validateConfiguration();

            return new TokenManager(
                clientId: $this->clientId,
                clientSecret: $this->clientSecret,
                tokenUrl: $this->tokenUrl,
            );
        });

        $this->app->singleton(TimrClientInterface::class, function ($app): TimrClient {
            $this->validateConfiguration();

            return new TimrClient(
                tokenManager: $app->make(TokenManager::class),
                baseUrl: $this->baseUrl,
            );
        });

        $this->app->singleton(ProjectTimeRepositoryInterface::class, ProjectTimeRepository::class);
        $this->app->singleton('timr', TimrService::class);
    }

    private function validateConfiguration(): void
    {
        $baseUrl = config('timr.base_url');
        $tokenUrl = config('timr.token_url');
        $clientId = config('timr.client_id');
        $clientSecret = config('timr.client_secret');

        if (! is_string($baseUrl) || $baseUrl === '' ||
            ! is_string($tokenUrl) || $tokenUrl === '' ||
            ! is_string($clientId) || $clientId === '' ||
            ! is_string($clientSecret) || $clientSecret === '') {
            throw new RuntimeException('Timr API configuration is missing or invalid');
        }

        $this->baseUrl = $baseUrl;
        $this->tokenUrl = $tokenUrl;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }
}
