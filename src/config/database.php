<?php

use Illuminate\Support\Str;

return [
    'default' => env('DB_CONNECTION', 'central'),

    'connections' => [
        'central' => [
            'driver' => 'mysql',
            'url' => env('DB_CENTRAL_URL'),
            'host' => env('DB_CENTRAL_HOST', 'db'),
            'port' => env('DB_CENTRAL_PORT', '3306'),
            'database' => env('DB_CENTRAL_DATABASE', 'cac_central'),
            'username' => env('DB_CENTRAL_USERNAME', 'user'),
            'password' => env('DB_CENTRAL_PASSWORD', ''),
            'unix_socket' => env('DB_CENTRAL_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                // Adicionado timeout para maior estabilidade
                PDO::ATTR_TIMEOUT => 5, 
            ]) : [],
        ],

        'tenant' => [
            'driver' => 'mysql',
            'host' => env('DB_TENANT_HOST', 'db'),
            'port' => env('DB_TENANT_PORT', '3306'),
            'database' => null, // O pacote preenche isso dinamicamente
            'username' => env('DB_TENANT_USERNAME', 'user'),
            'password' => env('DB_TENANT_PASSWORD', ''),
            'unix_socket' => env('DB_TENANT_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                // Adicionado timeout para falhar rápido em caso de intermitência 🎯
                PDO::ATTR_TIMEOUT => 5,
            ]) : [],
        ],

        // Outras conexões (sqlite, mariadb, pgsql, sqlsrv) podem ser mantidas se necessário
    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'none'),
            //'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'CACSystem')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', 'redis'),
            'username' => env('REDIS_USERNAME', 'default'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'scheme' => env('REDIS_SCHEME', 'tcp'),
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => false,
            ],
        ],
        'cache' => [
            'scheme' => env('REDIS_SCHEME', 'tcp'),
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', 'redis'),
            'username' => env('REDIS_USERNAME', 'default'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => false,
            ],
        ],
    ],
];
