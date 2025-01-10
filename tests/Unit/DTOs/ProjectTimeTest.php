<?php

declare(strict_types=1);

use DateTime;
use InvalidArgumentException;
use PhilHarmonie\Timr\DTOs\ProjectTime;

test('fromArray creates valid ProjectTime object', function () {
    $data = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 30,
        'break_times' => [
            ['start' => '2024-01-01T01:00:00Z', 'end' => '2024-01-01T02:00:00Z'],
        ],
        'changed' => true,
        'user' => ['id' => 1],
        'task' => ['id' => 1],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    $projectTime = ProjectTime::fromArray($data);
    expect($projectTime)->toBeInstanceOf(ProjectTime::class);
});

test('fromArray creates ProjectTime object with all optional fields', function () {
    $data = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'end' => '2024-01-01T08:00:00Z',
        'break_time_total_minutes' => 30,
        'break_times' => [
            ['start' => '2024-01-01T01:00:00Z', 'end' => '2024-01-01T02:00:00Z'],
        ],
        'duration' => ['type' => 'minutes', 'value' => 480],
        'changed' => true,
        'notes' => 'Test notes',
        'user' => ['id' => 1],
        'task' => ['id' => 1],
        'billable' => true,
        'start_location' => ['lat' => 0, 'lng' => 0],
        'end_location' => ['lat' => 1, 'lng' => 1],
        'start_platform' => 'web',
        'end_platform' => 'mobile',
        'last_modified' => '2024-01-01T00:00:00Z',
        'last_modified_by' => ['id' => 2],
        'status' => 'active',
    ];

    $projectTime = ProjectTime::fromArray($data);
    expect($projectTime)
        ->toBeInstanceOf(ProjectTime::class)
        ->and($projectTime->end)->toBeInstanceOf(DateTime::class)
        ->and($projectTime->duration)->toBeArray()
        ->and($projectTime->notes)->toBe('Test notes')
        ->and($projectTime->startLocation)->toBeArray()
        ->and($projectTime->endLocation)->toBeArray()
        ->and($projectTime->endPlatform)->toBe('mobile')
        ->and($projectTime->lastModifiedBy)->toBeArray();
});

test('fromArray throws exception for invalid date format', function () {
    $data = [
        'id' => 'test-id',
        'start' => 'invalid-date',  // Invalid date format
        'break_time_total_minutes' => 30,
        'break_times' => [],
        'changed' => true,
        'user' => [],
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data))->toThrow(Exception::class);
});

test('fromArray throws exception for null required field', function () {
    $data = [
        'id' => 'test-id',
        'start' => null,  // Required field is null
        'break_time_total_minutes' => 30,
        'break_times' => [],
        'changed' => true,
        'user' => [],
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data))
        ->toThrow(InvalidArgumentException::class, "Field 'start' must be a string");
});

test('assertString throws exception for missing field', function () {
    $data = [
        'id' => 'test-id',
        // start field is missing entirely
        'break_time_total_minutes' => 30,
        'break_times' => [],
        'changed' => true,
        'user' => [],
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data))
        ->toThrow(InvalidArgumentException::class, "Field 'start' must be a string");
});

test('assertBreakTimesArray handles various invalid scenarios', function () {
    // Test 1: break_times is not set
    $data1 = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 30,
        // break_times is missing
        'changed' => true,
        'user' => [],
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data1))
        ->toThrow(InvalidArgumentException::class, "Field 'break_times' must be an array");

    // Test 2: break_times is not an array
    $data2 = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 30,
        'break_times' => 'not-an-array',
        'changed' => true,
        'user' => [],
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data2))
        ->toThrow(InvalidArgumentException::class, "Field 'break_times' must be an array");

    // Test 3: break_times contains non-array element
    $data3 = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 30,
        'break_times' => ['not-an-array'],
        'changed' => true,
        'user' => [],
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data3))
        ->toThrow(InvalidArgumentException::class, 'Each break time entry must be an array at index 0');
});

test('assertStringOrNull handles various invalid cases', function () {
    $data = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'end' => 123, // Invalid: should be string or null
        'break_time_total_minutes' => 30,
        'break_times' => [],
        'changed' => true,
        'user' => [],
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data))
        ->toThrow(InvalidArgumentException::class, "Field 'end' must be a string or null");
});

test('assertInt throws exception for non-integer value', function () {
    $data = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => '30', // String instead of int
        'break_times' => [],
        'changed' => true,
        'user' => [],
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data))
        ->toThrow(InvalidArgumentException::class, "Field 'break_time_total_minutes' must be an integer");
});

test('assertInt throws exception for missing integer field', function () {
    $data = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        // break_time_total_minutes is missing
        'break_times' => [],
        'changed' => true,
        'user' => [],
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data))
        ->toThrow(InvalidArgumentException::class, "Field 'break_time_total_minutes' must be an integer");
});

test('assertBool throws exception for non-boolean value', function () {
    $data = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 30,
        'break_times' => [],
        'changed' => 1, // Number instead of boolean
        'user' => [],
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data))
        ->toThrow(InvalidArgumentException::class, "Field 'changed' must be a boolean");
});

test('assertBool throws exception for missing boolean field', function () {
    $data = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 30,
        'break_times' => [],
        // changed is missing
        'user' => [],
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data))
        ->toThrow(InvalidArgumentException::class, "Field 'changed' must be a boolean");
});

test('assertArray throws exception for missing required array', function () {
    $data = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 30,
        'break_times' => [],
        'changed' => true,
        // user field is missing
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data))
        ->toThrow(InvalidArgumentException::class, "Field 'user' must be an array");
});

test('assertArray throws exception for non-array value', function () {
    $data = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 30,
        'break_times' => [],
        'changed' => true,
        'user' => 'not-an-array',  // String instead of array
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data))
        ->toThrow(InvalidArgumentException::class, "Field 'user' must be an array");
});

test('assertArrayOrNull handles various cases correctly', function () {
    // Test 1: Valid null value
    $data1 = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 30,
        'break_times' => [],
        'changed' => true,
        'user' => [],
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'duration' => null,
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    $projectTime1 = ProjectTime::fromArray($data1);
    expect($projectTime1->duration)->toBeNull();

    // Test 2: Invalid non-array value
    $data2 = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 30,
        'break_times' => [],
        'changed' => true,
        'user' => [],
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'duration' => 'not-an-array',  // String instead of array or null
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data2))
        ->toThrow(InvalidArgumentException::class, "Field 'duration' must be an array or null");
});

// Test für fehlenden Array-Wert
test('assertArray throws exception when field is missing', function () {
    $data = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 30,
        'break_times' => [],
        'changed' => true,
        // user fehlt komplett
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data))
        ->toThrow(InvalidArgumentException::class, "Field 'user' must be an array");
});

// Test für nicht existierenden break_times Array
test('assertBreakTimesArray throws exception when field is missing', function () {
    $data = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 30,
        // break_times fehlt komplett
        'changed' => true,
        'user' => [],
        'task' => [],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    expect(fn () => ProjectTime::fromArray($data))
        ->toThrow(InvalidArgumentException::class, "Field 'break_times' must be an array");
});

// Test für die optionale Duration als null
test('fromArray creates ProjectTime object with null duration', function () {
    $data = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 30,
        'break_times' => [],
        'duration' => null,  // Explizit null setzen
        'changed' => true,
        'user' => ['id' => 1],
        'task' => ['id' => 1],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    $projectTime = ProjectTime::fromArray($data);
    expect($projectTime->duration)->toBeNull();
});

// Test für den Fall, dass duration nicht im Array vorhanden ist
test('fromArray creates ProjectTime object without duration field', function () {
    $data = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 30,
        'break_times' => [],
        // duration field ist komplett nicht vorhanden
        'changed' => true,
        'user' => ['id' => 1],
        'task' => ['id' => 1],
        'billable' => true,
        'start_platform' => 'web',
        'last_modified' => '2024-01-01T00:00:00Z',
        'status' => 'active',
    ];

    $projectTime = ProjectTime::fromArray($data);
    expect($projectTime->duration)->toBeNull();
});

test('fromArray returns null for missing optional string fields', function () {
    $data = [
        'id' => 'test-id',
        'start' => '2024-01-01T00:00:00Z',
        'start_platform' => 'web',
        'status' => 'active',
        'changed' => true,
        'billable' => true,
        'break_times' => [],
        'user' => ['id' => 1],
        'task' => ['id' => 1],
        'last_modified' => '2024-01-01T00:00:00Z',
        'break_time_total_minutes' => 30,
        // Felder wie `notes` und `end_platform` fehlen
    ];

    $projectTime = ProjectTime::fromArray($data);

    expect($projectTime->notes)->toBeNull();
    expect($projectTime->endPlatform)->toBeNull();
});
