<?php

declare(strict_types=1);

namespace Tests\Unit;

use PhilHarmonie\Timr\DTOs\ProjectTimeCollection;
use PhilHarmonie\Timr\Service\TimrService;
use PhilHarmonie\Timr\Timr;

test('facade resolves to timr service instance', function () {
    $app = app();

    $repository = new class implements \PhilHarmonie\Timr\Contracts\ProjectTimeRepositoryInterface
    {
        public function list(array $filters = []): ProjectTimeCollection
        {
            return new ProjectTimeCollection([], null);
        }
    };

    $service = new TimrService($repository);
    $app->instance('timr', $service);

    expect(Timr::getFacadeRoot())->toBe($service);
});
