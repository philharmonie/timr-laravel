<?php

declare(strict_types=1);

namespace Tests\Traits;

use PhilHarmonie\Timr\ServiceProvider;

trait WithServiceProvider
{
    protected function getPackageProviders($app): array
    {
        return [ServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('timr.base_url', 'https://api.timr.com/v0.2/');
        $app['config']->set('timr.token', 'test-token');
    }
}
