<?php

return [
    'keycloak' => [
        'realm' => env('KEYCLOAK_REALM'),
        'base_url' => env('KEYCLOAK_BASE_URL'),
        'client_id' => env('KEYCLOAK_CLIENT_ID'),
        'client_uuid' => env('KEYCLOAK_CLIENT_UUID'),
        'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
        'master_client_id' => env('KEYCLOAK_MASTER_CLIENT_ID'),
        'master_client_secret' => env('KEYCLOAK_MASTER_CLIENT_SECRET'),
        'master_username' => env('KEYCLOAK_MASTER_USERNAME'),
        'master_password' => env('KEYCLOAK_MASTER_PASSWORD'),
        'routes' => [
            'login' => 'realms/{realm}/protocol/openid-connect/token',
            'masterLogin' => 'realms/master/protocol/openid-connect/token',
            'createUser' => 'admin/realms/{realm}/users',
            'mapRole' => 'admin/realms/{realm}/users/{id}/role-mappings/clients/{client}',
            'resetPassword' => 'admin/realms/{realm}/users/{id}/reset-password',
            'userRole' => 'admin/realms/{realm}/users/{id}/role-mappings/clients/{client}',
        ],
        'roles' => [
            [
                'id' => env('KEYCLOAK_ROLE_CONSULTANT_ID'),
                'name' => 'consultant',
            ],
            [
                'id' => env('KEYCLOAK_ROLE_DISTRIBUTOR_ADMIN_ID'),
                'name' => 'distributor_admin',
            ],
            [
                'id' => env('KEYCLOAK_ROLE_DISTRIBUTOR_MANAGER_ID'),
                'name' => 'distributor_fund_manager',
            ],
            [
                'id' => env('KEYCLOAK_ROLE_FIMM_ID'),
                'name' => 'fimm_rd',
            ]
        ]
    ],
    'module0' => [
        'verifyTac' => env('VERIFY_TAC_ENDPOINT'),
    ]
];
