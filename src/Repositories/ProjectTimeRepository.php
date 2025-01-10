<?php

declare(strict_types=1);

namespace PhilHarmonie\Timr\Repositories;

use PhilHarmonie\Timr\Contracts\ProjectTimeRepositoryInterface;
use PhilHarmonie\Timr\Contracts\TimrClientInterface;
use PhilHarmonie\Timr\DTOs\ProjectTimeCollection;

final readonly class ProjectTimeRepository implements ProjectTimeRepositoryInterface
{
    public function __construct(
        private TimrClientInterface $client
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters = []): ProjectTimeCollection
    {
        $response = $this->client->get('project-times', $filters);

        return ProjectTimeCollection::fromArray($response);
    }
}
