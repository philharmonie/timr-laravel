<?php

declare(strict_types=1);

namespace PhilHarmonie\Timr\Service;

use PhilHarmonie\Timr\Contracts\ProjectTimeRepositoryInterface;
use PhilHarmonie\Timr\DTOs\ProjectTime;
use PhilHarmonie\Timr\DTOs\ProjectTimeCollection;

final readonly class TimrService
{
    public function __construct(
        private ProjectTimeRepositoryInterface $projectTimeRepository
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function projectTimes(array $filters = []): ProjectTimeCollection
    {
        return $this->projectTimeRepository->list($filters);
    }

    /**
     * Fetch every page of project times for the given filters.
     *
     * The API caps a page at 500 entries, so anything relying on a complete
     * result set has to follow `next_page_token` until it comes back null.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, ProjectTime>
     */
    public function allProjectTimes(array $filters = []): array
    {
        $items = [];
        $pageToken = null;
        $seenTokens = [];

        do {
            $collection = $this->projectTimeRepository->list(
                $pageToken === null ? $filters : [...$filters, 'page_token' => $pageToken]
            );

            $items = [...$items, ...$collection->getItems()];
            $pageToken = $collection->nextPageToken;

            // A token we already followed would loop forever.
            if ($pageToken !== null && isset($seenTokens[$pageToken])) {
                break;
            }

            if ($pageToken !== null) {
                $seenTokens[$pageToken] = true;
            }
        } while ($pageToken !== null);

        return $items;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateProjectTime(string $id, array $data): array
    {
        return $this->projectTimeRepository->update($id, $data);
    }
}
