<?php

declare(strict_types=1);

return [
    'base_url' => env('TIMR_BASE_URL', 'https://api.timr.com/v0.2/'),
    'token_url' => env('TIMR_TOKEN_URL', 'https://system.timr.com/id/oauth2/token'),
    'client_id' => env('TIMR_CLIENT_ID'),
    'client_secret' => env('TIMR_CLIENT_SECRET'),
];
