<?php

declare(strict_types=1);

namespace PhilHarmonie\Timr;

use Illuminate\Support\Facades\Facade;
use YourVendor\LaravelTimr\DTOs\ProjectTimeCollection;

/**
 * @method static ProjectTimeCollection projectTimes(array $filters = [])
 */
final class Timr extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'timr';
    }
}
