<?php

declare(strict_types=1);

use PhilHarmonie\Timr\Contracts\ProjectTimeRepositoryInterface;
use PhilHarmonie\Timr\DTOs\ProjectTimeCollection;
use PhilHarmonie\Timr\Service\TimrService;

test('project times returns collection from repository', function () {
    $repository = Mockery::mock(ProjectTimeRepositoryInterface::class);
    $expectedCollection = new ProjectTimeCollection([], null);

    $repository->shouldReceive('list')
        ->with(['filter' => 'test'])
        ->once()
        ->andReturn($expectedCollection);

    $service = new TimrService($repository);
    $result = $service->projectTimes(['filter' => 'test']);

    expect($result)->toBe($expectedCollection);
});
