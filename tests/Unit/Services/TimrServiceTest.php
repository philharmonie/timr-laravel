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

test('updateProjectTime calls repository and returns updated data', function () {
    $repository = Mockery::mock(ProjectTimeRepositoryInterface::class);
    $service = new TimrService($repository);

    $id = 'test-id';
    $data = ['key' => 'value'];
    $expectedResponse = ['id' => $id, 'updated_data' => $data];

    $repository->shouldReceive('update')
        ->with($id, $data)
        ->once()
        ->andReturn($expectedResponse);

    $result = $service->updateProjectTime($id, $data);

    expect($result)->toBe($expectedResponse);
});
