<?php

declare(strict_types=1);

namespace PhilHarmonie\Timr\Exceptions;

use Exception;
use Throwable;

final class TimrException extends Exception
{
    /**
     * @param  array<int, array<string, mixed>>  $errors  Field level validation details
     */
    public function __construct(
        string $message,
        public readonly ?int $statusCode = null,
        public readonly ?string $type = null,
        public readonly ?string $title = null,
        public readonly ?string $detail = null,
        public readonly ?string $instance = null,
        public readonly ?string $traceId = null,
        public readonly array $errors = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode ?? 0, $previous);
    }

    /**
     * Build an exception from an RFC 9457 problem details document.
     *
     * @param  array<string, mixed>  $problem
     */
    public static function fromProblemDetail(array $problem, int $statusCode, ?Throwable $previous = null): self
    {
        $detail = self::stringOrNull($problem, 'detail');
        $title = self::stringOrNull($problem, 'title');
        $type = self::stringOrNull($problem, 'type');
        $traceId = self::stringOrNull($problem, 'trace_id');

        $message = sprintf(
            'Timr API request failed: %s (HTTP %d%s)',
            $detail ?? $title ?? $type ?? 'unknown error',
            $statusCode,
            $traceId === null ? '' : ", trace_id {$traceId}",
        );

        return new self(
            message: $message,
            statusCode: $statusCode,
            type: $type,
            title: $title,
            detail: $detail,
            instance: self::stringOrNull($problem, 'instance'),
            traceId: $traceId,
            errors: self::errorsFrom($problem),
            previous: $previous,
        );
    }

    /**
     * @param  array<string, mixed>  $problem
     */
    private static function stringOrNull(array $problem, string $key): ?string
    {
        return isset($problem[$key]) && is_string($problem[$key]) ? $problem[$key] : null;
    }

    /**
     * @param  array<string, mixed>  $problem
     * @return array<int, array<string, mixed>>
     */
    private static function errorsFrom(array $problem): array
    {
        if (! isset($problem['errors']) || ! is_array($problem['errors'])) {
            return [];
        }

        $errors = [];
        foreach ($problem['errors'] as $error) {
            if (is_array($error)) {
                $errors[] = $error;
            }
        }

        return $errors;
    }
}
