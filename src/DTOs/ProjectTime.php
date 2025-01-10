<?php

declare(strict_types=1);

namespace PhilHarmonie\Timr\DTOs;

use DateTime;
use InvalidArgumentException;

final readonly class ProjectTime
{
    /**
     * @param  array<int, array<string, mixed>>  $breakTimes  Break time information
     * @param  array<string, mixed>|null  $duration  Duration information including type and minutes
     * @param  array<string, mixed>  $user  User information
     * @param  array<string, mixed>  $task  Task information
     * @param  array<string, mixed>|null  $startLocation  Start location data
     * @param  array<string, mixed>|null  $endLocation  End location data
     * @param  array<string, mixed>|null  $lastModifiedBy  User who last modified the record
     */
    public function __construct(
        public string $id,
        public DateTime $start,
        public ?DateTime $end,
        public int $breakTimeTotal,
        public array $breakTimes,
        public ?array $duration,
        public bool $changed,
        public ?string $notes,
        public array $user,
        public array $task,
        public bool $billable,
        public ?array $startLocation,
        public ?array $endLocation,
        public string $startPlatform,
        public ?string $endPlatform,
        public DateTime $lastModified,
        public ?array $lastModifiedBy,
        public string $status,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $data): self
    {
        // Required string fields validation with type assertion
        $id = self::assertString($data, 'id');
        $startPlatform = self::assertString($data, 'start_platform');
        $status = self::assertString($data, 'status');

        // Required boolean fields validation with type assertion
        $changed = self::assertBool($data, 'changed');
        $billable = self::assertBool($data, 'billable');

        // Required array fields validation with type assertion
        $breakTimes = self::assertBreakTimesArray($data);
        $user = self::assertArray($data, 'user');
        $task = self::assertArray($data, 'task');

        // Required DateTime string fields validation
        $startStr = self::assertString($data, 'start');
        $lastModifiedStr = self::assertString($data, 'last_modified');

        // Break time total validation
        $breakTimeTotal = self::assertInt($data, 'break_time_total_minutes');

        // Optional fields validation
        $endStr = isset($data['end']) ? self::assertStringOrNull($data, 'end') : null;
        $endPlatform = isset($data['end_platform']) ? self::assertStringOrNull($data, 'end_platform') : null;
        $notes = isset($data['notes']) ? self::assertStringOrNull($data, 'notes') : null;

        // Optional array fields
        $duration = isset($data['duration']) ? self::assertArrayOrNull($data, 'duration') : null;
        $startLocation = isset($data['start_location']) ? self::assertArrayOrNull($data, 'start_location') : null;
        $endLocation = isset($data['end_location']) ? self::assertArrayOrNull($data, 'end_location') : null;
        $lastModifiedBy = isset($data['last_modified_by']) ? self::assertArrayOrNull($data, 'last_modified_by') : null;

        return new self(
            id: $id,
            start: new DateTime($startStr),
            end: $endStr ? new DateTime($endStr) : null,
            breakTimeTotal: $breakTimeTotal,
            breakTimes: $breakTimes,
            duration: $duration,
            changed: $changed,
            notes: $notes,
            user: $user,
            task: $task,
            billable: $billable,
            startLocation: $startLocation,
            endLocation: $endLocation,
            startPlatform: $startPlatform,
            endPlatform: $endPlatform,
            lastModified: new DateTime($lastModifiedStr),
            lastModifiedBy: $lastModifiedBy,
            status: $status,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>
     *
     * @throws InvalidArgumentException
     */
    private static function assertBreakTimesArray(array $data): array
    {
        if (! isset($data['break_times']) || ! is_array($data['break_times'])) {
            throw new InvalidArgumentException("Field 'break_times' must be an array");
        }

        $result = [];
        foreach ($data['break_times'] as $index => $breakTime) {
            if (! is_array($breakTime)) {
                throw new InvalidArgumentException("Each break time entry must be an array at index {$index}");
            }
            $result[] = $breakTime;
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidArgumentException
     */
    private static function assertString(array $data, string $field): string
    {
        if (! isset($data[$field]) || ! is_string($data[$field])) {
            throw new InvalidArgumentException("Field '{$field}' must be a string");
        }

        return $data[$field];
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidArgumentException
     */
    private static function assertStringOrNull(array $data, string $field): ?string
    {
        if (! isset($data[$field])) {
            return null;
        }
        if (! is_string($data[$field])) {
            throw new InvalidArgumentException("Field '{$field}' must be a string or null");
        }

        return $data[$field];
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidArgumentException
     */
    private static function assertBool(array $data, string $field): bool
    {
        if (! isset($data[$field]) || ! is_bool($data[$field])) {
            throw new InvalidArgumentException("Field '{$field}' must be a boolean");
        }

        return $data[$field];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException
     */
    private static function assertArray(array $data, string $field): array
    {
        if (! isset($data[$field]) || ! is_array($data[$field])) {
            throw new InvalidArgumentException("Field '{$field}' must be an array");
        }

        return $data[$field];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     *
     * @throws InvalidArgumentException
     */
    private static function assertArrayOrNull(array $data, string $field): ?array
    {
        if (! isset($data[$field])) {
            return null;
        }
        if (! is_array($data[$field])) {
            throw new InvalidArgumentException("Field '{$field}' must be an array or null");
        }

        return $data[$field];
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidArgumentException
     */
    private static function assertInt(array $data, string $field): int
    {
        if (! isset($data[$field]) || ! is_int($data[$field])) {
            throw new InvalidArgumentException("Field '{$field}' must be an integer");
        }

        return $data[$field];
    }
}
