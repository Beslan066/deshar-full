<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
    'allowed_origins' => [
        'https://desharing.ru',
        'https://www.desharing.ru',
        'http://localhost:3000', // только для разработки
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
