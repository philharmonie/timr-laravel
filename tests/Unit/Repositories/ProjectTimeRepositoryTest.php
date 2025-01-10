<?php

declare(strict_types=1);

use PhilHarmonie\Timr\Contracts\TimrClientInterface;
use PhilHarmonie\Timr\DTOs\ProjectTimeCollection;
use PhilHarmonie\Timr\Repositories\ProjectTimeRepository;

test('list returns project time collection', function () {
    $client = Mockery::mock(TimrClientInterface::class);
    $responseData = [
        'data' => [
            [
                'id' => '123',
                'start' => '2024-01-09T10:00:00+00:00',
                'break_time_total_minutes' => 30,
                'break_times' => [],
                'changed' => true,
                'user' => ['id' => '456'],
                'task' => ['id' => '789'],
                'billable' => true,
                'start_platform' => 'timr_web',
                'last_modified' => '2024-01-09T18:00:00+00:00',
                'status' => 'completed',
            ],
        ],
    ];

    $client->shouldReceive('get')
        ->with('project-times', ['filter' => 'test'])
        ->once()
        ->andReturn($responseData);

    $repository = new ProjectTimeRepository($client);
    $result = $repository->list(['filter' => 'test']);

    expect($result)
        ->toBeInstanceOf(ProjectTimeCollection::class)
        ->and($result->getItems())
        ->toHaveCount(1);
});
