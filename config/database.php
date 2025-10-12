<?php
return [
    'host' => getenv('LM_DB_HOST') ?: 'localhost',
    'name' => getenv('LM_DB_NAME') ?: 'u420143207_LM_landing',
    'user' => getenv('LM_DB_USER') ?: 'u420143207_lmlanding',
    'password' => getenv('LM_DB_PASSWORD') ?: 'Levelminds@2024',
    'charset' => 'utf8mb4',
];
