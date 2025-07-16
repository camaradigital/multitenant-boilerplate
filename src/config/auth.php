<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Guarda de autenticação para os tenants.
        'tenant' => [
            'driver' => 'session',
            'provider' => 'tenant_users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        // Provider para os usuários da aplicação central (landlord)
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // Provider para os usuários dos tenants
        'tenant_users' => [
            'driver' => 'eloquent',
            // **ATUALIZADO**: Aponta para o seu novo modelo de usuário do tenant.
            'model' => App\Models\Tenant\User::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        // Broker para os usuários centrais (landlord)
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'connection' => 'central',  // Adição opcional: Especifica a conexão central
            'expire' => 60,
            'throttle' => 60,
        ],

        // Broker para os usuários dos tenants
        'tenant_users' => [
            'provider' => 'tenant_users',
            'table' => 'password_reset_tokens',
            'connection' => 'tenant', // Usa a conexão de DB do tenant
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => 10800,

];
