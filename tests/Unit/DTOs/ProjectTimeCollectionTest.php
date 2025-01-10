<?php

declare(strict_types=1);

use PhilHarmonie\Timr\DTOs\ProjectTimeCollection;

beforeEach(function () {
    $this->validData = [
        'data' => [
            [
                'id' => '123',
                'start' => '2024-01-09T10:00:00+00:00',
                'end' => '2024-01-09T18:00:00+00:00',
                'break_time_total_minutes' => 30,
                'break_times' => [],
                'changed' => true,
                'notes' => 'Test notes',
                'user' => ['id' => '456'],
                'task' => ['id' => '789'],
                'billable' => true,
                'start_platform' => 'timr_web',
                'last_modified' => '2024-01-09T18:00:00+00:00',
                'status' => 'completed',
            ],
        ],
        'next_page_token' => 'next-page-token',
    ];
});

test('creates collection from valid data', function () {
    $validData = [
        'data' => [
            [
                'id' => '123',
                'start' => '2024-01-09T10:00:00+00:00',
                'end' => '2024-01-09T18:00:00+00:00',
                'break_time_total_minutes' => 30,
                'break_times' => [],
                'changed' => true,
                'notes' => 'Test notes',
                'user' => ['id' => '456'],
                'task' => ['id' => '789'],
                'billable' => true,
                'start_platform' => 'timr_web',
                'last_modified' => '2024-01-09T18:00:00+00:00',
                'status' => 'completed',
            ],
        ],
        'next_page_token' => 'next-page-token',
    ];

    $collection = ProjectTimeCollection::fromArray($validData);

    expect($collection)
        ->toBeInstanceOf(ProjectTimeCollection::class)
        ->getItems()->toBeArray()->toHaveCount(1)
        ->and($collection->nextPageToken)
        ->toBe('next-page-token');
});

test('handles malformed project time', function () {
    $this->validData['data'][] = [
        // Missing required fields
        'id' => '124',
    ];

    expect(fn () => ProjectTimeCollection::fromArray($this->validData))
        ->toThrow(InvalidArgumentException::class);
});

test('handles empty data array', function () {
    $this->validData['data'] = [];
    $collection = ProjectTimeCollection::fromArray($this->validData);

    expect($collection->getItems())
        ->toBeArray()
        ->toBeEmpty();
});

test('handles missing next page token', function () {
    unset($this->validData['next_page_token']);
    $collection = ProjectTimeCollection::fromArray($this->validData);

    expect($collection->nextPageToken)
        ->toBeNull();
});

test('handles null next page token', function () {
    $this->validData['next_page_token'] = null;
    $collection = ProjectTimeCollection::fromArray($this->validData);

    expect($collection->nextPageToken)
        ->toBeNull();
});

test('throws exception for invalid next page token type', function () {
    $this->validData['next_page_token'] = ['not a string'];

    expect(fn () => ProjectTimeCollection::fromArray($this->validData))
        ->toThrow(InvalidArgumentException::class);
});

test('throws exception for missing data array', function () {
    unset($this->validData['data']);

    expect(fn () => ProjectTimeCollection::fromArray($this->validData))
        ->toThrow(InvalidArgumentException::class);
});

test('throws exception for invalid data array', function () {
    $this->validData['data'] = 'not an array';

    expect(fn () => ProjectTimeCollection::fromArray($this->validData))
        ->toThrow(InvalidArgumentException::class);
});
