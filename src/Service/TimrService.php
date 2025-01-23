<?php

declare(strict_types=1);

namespace PhilHarmonie\Timr\Service;

use PhilHarmonie\Timr\Contracts\ProjectTimeRepositoryInterface;
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
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateProjectTime(string $id, array $data): array
    {
        return $this->projectTimeRepository->update($id, $data);
    }
}
