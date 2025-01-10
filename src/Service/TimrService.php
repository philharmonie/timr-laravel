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
}
