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

test('allProjectTimes follows next_page_token until the last page', function () {
    $repository = Mockery::mock(ProjectTimeRepositoryInterface::class);

    $entry = fn (string $id): array => [
        'id' => $id,
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 0,
        'break_times' => [],
        'changed' => false,
        'user' => ['id' => 1],
        'task' => ['id' => 1],
        'billable' => true,
        'start_platform' => 'timr_web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'changeable',
    ];

    $repository->shouldReceive('list')
        ->with(['billable' => true])
        ->once()
        ->andReturn(new ProjectTimeCollection([$entry('a')], 'token-2'));

    $repository->shouldReceive('list')
        ->with(['billable' => true, 'page_token' => 'token-2'])
        ->once()
        ->andReturn(new ProjectTimeCollection([$entry('b')], null));

    $service = new TimrService($repository);

    expect(array_map(fn ($item) => $item->id, $service->allProjectTimes(['billable' => true])))
        ->toBe(['a', 'b']);
});

test('allProjectTimes stops when the API repeats a page token', function () {
    $repository = Mockery::mock(ProjectTimeRepositoryInterface::class);

    $repository->shouldReceive('list')
        ->with([])
        ->once()
        ->andReturn(new ProjectTimeCollection([], 'loop'));

    $repository->shouldReceive('list')
        ->with(['page_token' => 'loop'])
        ->once()
        ->andReturn(new ProjectTimeCollection([], 'loop'));

    $service = new TimrService($repository);

    expect($service->allProjectTimes())->toBe([]);
});
