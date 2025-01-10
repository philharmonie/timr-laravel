<?php

declare(strict_types=1);

namespace PhilHarmonie\Timr\DTOs;

use InvalidArgumentException;

final readonly class ProjectTimeCollection
{
    /** @var array<int, ProjectTime> */
    private array $items;

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function __construct(
        array $items,
        public ?string $nextPageToken
    ) {
        $this->items = array_map(
            fn (array $item): ProjectTime => ProjectTime::fromArray($item),
            $items
        );
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $data): self
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            throw new InvalidArgumentException('Invalid or missing data array');
        }

        $nextPageToken = null;
        if (isset($data['next_page_token']) && ! is_null($data['next_page_token'])) {
            if (! is_string($data['next_page_token'])) {
                throw new InvalidArgumentException('next_page_token must be a string or null');
            }
            $nextPageToken = $data['next_page_token'];
        }

        return new self(
            items: $data['data'],
            nextPageToken: $nextPageToken
        );
    }

    /**
     * @return array<int, ProjectTime>
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
