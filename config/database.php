<?php

use Illuminate\Support\Str;

return [

    'default' => 'mysql',

    'connections' => [

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '192.168.2.56'),
            'port' => env('DB_PORT', '6000'),
            'database' => env('DB_DATABASE', 'module1'),
            'username' => env('DB_USERNAME', 'fimm_user2'),
            'password' => env('DB_PASSWORD', 'pa55w0rd'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'module0' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '192.168.2.56'),
            'port' => env('DB_PORT', '6000'),
            'database' => env('DB_DATABASE0', 'module1'),
            'username' => env('DB_USERNAME', 'fimm_user2'),
            'password' => env('DB_PASSWORD', 'pa55w0rd'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'module1' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '192.168.2.56'),
            'port' => env('DB_PORT', '6000'),
            'database' => env('DB_DATABASE1', 'module1'),
            'username' => env('DB_USERNAME', 'fimm_user2'),
            'password' => env('DB_PASSWORD', 'pa55w0rd'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'module2' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '192.168.2.56'),
            'port' => env('DB_PORT', '6000'),
            'database' => env('DB_DATABASE2', 'module1'),
            'username' => env('DB_USERNAME', 'fimm_user2'),
            'password' => env('DB_PASSWORD', 'pa55w0rd'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'module3' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '192.168.2.56'),
            'port' => env('DB_PORT', '6000'),
            'database' => env('DB_DATABASE3', 'module1'),
            'username' => env('DB_USERNAME', 'fimm_user2'),
            'password' => env('DB_PASSWORD', 'pa55w0rd'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'module4' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '192.168.2.56'),
            'port' => env('DB_PORT', '6000'),
            'database' => env('DB_DATABASE4', 'module1'),
            'username' => env('DB_USERNAME', 'fimm_user2'),
            'password' => env('DB_PASSWORD', 'pa55w0rd'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'module5' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '192.168.2.56'),
            'port' => env('DB_PORT', '6000'),
            'database' => env('DB_DATABASE5', 'module1'),
            'username' => env('DB_USERNAME', 'fimm_user2'),
            'password' => env('DB_PASSWORD', 'pa55w0rd'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'module6' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '192.168.2.56'),
            'port' => env('DB_PORT', '6000'),
            'database' => env('DB_DATABASE6', 'module1'),
            'username' => env('DB_USERNAME', 'fimm_user2'),
            'password' => env('DB_PASSWORD', 'pa55w0rd'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'module7' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '192.168.2.56'),
            'port' => env('DB_PORT', '6000'),
            'database' => env('DB_DATABASE7', 'module1'),
            'username' => env('DB_USERNAME', 'fimm_user2'),
            'password' => env('DB_PASSWORD', 'pa55w0rd'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'mysql-setting' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '192.168.2.56'),
            'port' => env('DB_PORT', '6000'),
            'database' => env('DB_SETTING', 'module1'),
            'username' => env('DB_USERNAME', 'fimm_user2'),
            'password' => env('DB_PASSWORD', 'pa55w0rd'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
        'finance_management' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '192.168.2.56'),
            'port' => env('DB_PORT', '6000'),
            'database' => env('DB_DATABASE6', 'module6'),
            'username' => env('DB_USERNAME', 'fimm_user2'),
            'password' => env('DB_PASSWORD', 'pa55w0rd'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
