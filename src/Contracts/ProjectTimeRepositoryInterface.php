<?php

declare(strict_types=1);

namespace PhilHarmonie\Timr\Contracts;

use PhilHarmonie\Timr\DTOs\ProjectTimeCollection;

interface ProjectTimeRepositoryInterface
{
    /**
     * Get project times with optional filters
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters = []): ProjectTimeCollection;

    /**
     * Update a project time by ID
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(string $id, array $data): array;
}
