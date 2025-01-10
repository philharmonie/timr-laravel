<?php

declare(strict_types=1);

namespace PhilHarmonie\Timr\Contracts;

interface TimrClientInterface
{
    /**
     * Send a GET request to the timr API
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function get(string $endpoint, array $params = []): array;
}
