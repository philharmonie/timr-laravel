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
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/timr.php',
            'timr'
        );

        $baseUrl = config('timr.base_url');
        $tokenUrl = config('timr.token_url');
        $clientId = config('timr.client_id');
        $clientSecret = config('timr.client_secret');

        if (! is_string($baseUrl) || ! is_string($tokenUrl) || ! is_string($clientId) || ! is_string($clientSecret)) {
            throw new RuntimeException('Timr API configuration is missing or invalid');
        }

        $this->app->singleton(TokenManager::class, fn (): TokenManager => new TokenManager(
            clientId: $clientId,
            clientSecret: $clientSecret,
            tokenUrl: $tokenUrl,
        ));

        $this->app->singleton(TimrClientInterface::class, fn ($app): TimrClient => new TimrClient(
            tokenManager: $app->make(TokenManager::class),
            baseUrl: $baseUrl,
        ));

        $this->app->singleton(ProjectTimeRepositoryInterface::class, ProjectTimeRepository::class);
        $this->app->singleton('timr', TimrService::class);
    }
}
