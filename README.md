# Timr API Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/philharmonie/laravel-timr.svg?style=flat-square)](https://packagist.org/packages/philharmonie/laravel-timr)  
[![Total Downloads](https://img.shields.io/packagist/dt/philharmonie/laravel-timr.svg?style=flat-square)](https://packagist.org/packages/philharmonie/laravel-timr)  
[![License](https://img.shields.io/packagist/l/philharmonie/laravel-timr.svg?style=flat-square)](https://packagist.org/packages/philharmonie/laravel-timr)

## About

This package provides a foundation for integrating with the Timr API. Currently, only the `Project-Times` endpoint is implemented. Contributions to extend the functionality are highly encouraged and welcome!

## Requirements

- PHP ^8.2
- Laravel ^10.0
- Guzzle ^7.0 or ^8.0

## Installation

You can install the package via composer:

```
composer require philharmonie/laravel-timr
```

### Service Provider

The service provider is automatically registered using Laravel's auto-discovery feature. If you need to register it manually, add the following line to the `providers` array in `config/app.php`:

```
PhilHarmonie\LaravelTimr\ServiceProvider::class,
```

## Configuration

Publish the configuration file:

```
php artisan vendor:publish --tag="timr-config"
```

Add your Timr API credentials to your `.env` file:

```
TIMR_BASE_URL=https://api.timr.com/v0.2/
TIMR_CLIENT_ID=your-client-id
TIMR_CLIENT_SECRET=your-client-secret
TIMR_TOKEN_URL=https://api.timr.com/v0.2/token
```

## Usage

### Project Times

#### Using the Facade

```php
use PhilHarmonie\Timr\Timr;

// List project times
$projectTimes = Timr::projectTimes();

// List project times with filters
$projectTimes = Timr::projectTimes([
    'start_from' => '2025-01-01',
    'users' => ['user-id-1', 'user-id-2'],
    'billable' => true
]);

// Access the collection
foreach ($projectTimes->getItems() as $projectTime) {
    echo $projectTime->id;
    echo $projectTime->start->format('Y-m-d H:i:s');
    echo $projectTime->billable ? 'Billable' : 'Not billable';
}
```

#### Using the Service Directly

If you prefer dependency injection:

```php
use PhilHarmonie\Timr\Service\TimrService;

class YourController
{
    public function __construct(
        private readonly TimrService $timrService
    ) {}

    public function index()
    {
        $projectTimes = $this->timrService->projectTimes([
            'start_from' => '2025-01-01'
        ]);
    }
}
```

#### Direct Client Usage

If you need more control, you can use the client directly:

```php
use PhilHarmonie\Timr\Contracts\TimrClientInterface;

$client = app(TimrClientInterface::class);

// GET request
$response = $client->get('project-times', ['start_from' => '2025-01-01']);
```

## Extending the Package

The package is designed to be easily extensible. To add support for additional Timr API endpoints:

1. Create a new repository interface and implementation:

```php
namespace YourApp\Repositories;

use PhilHarmonie\Timr\Contracts\TimrClientInterface;

class YourRepository implements YourRepositoryInterface
{
    public function __construct(
        private readonly TimrClientInterface $client
    ) {}
}
```

2. Add your repository to the service provider:

```php
$this->app->singleton(YourRepositoryInterface::class, YourRepository::class);
```

3. Extend the `TimrService` with your new methods:

```php
class TimrService
{
    public function yourNewMethod(): mixed
    {
        return $this->yourRepository->someMethod();
    }
}
```

## Testing

Run the following command to test the package:

```
composer test
```

This will run:

- Code style checks (Pint)
- Static analysis (PHPStan)
- Unit tests (Pest)
- Refactoring checks (Rector)

You can also run individual test commands:

```
composer test:lint    # Run Laravel Pint
composer test:types   # Run PHPStan
composer test:unit    # Run Pest tests
composer test:refacto # Run Rector
```

## Contributing

Please see `CONTRIBUTING.md` for details.

## Security

If you discover any security related issues, please email phil@harmonie.media instead of using the issue tracker.

## Credits

- [Phil Harmonie](https://github.com/philharmonie)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see `LICENSE.md` for more information.
