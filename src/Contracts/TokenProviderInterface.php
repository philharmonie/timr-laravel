<?php

declare(strict_types=1);

namespace PhilHarmonie\Timr\Contracts;

interface TokenProviderInterface
{
    public function getToken(): string;
}
