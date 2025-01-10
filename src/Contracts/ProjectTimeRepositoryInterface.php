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
}
