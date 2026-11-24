<?php

return [
    /*
    | Permission groups are the single source of truth for the RBAC matrix.
    | Group labels and permission labels are translated in lang/{locale}/permissions.php.
    */
    'groups' => [
        'dashboard' => [
            'label' => 'permissions.groups.dashboard',
            'permissions' => ['dashboard.view'],
        ],
        'crm' => [
            'label' => 'permissions.groups.crm',
            'permissions' => ['customers.view', 'customers.manage'],
        ],
        'suppliers' => [
            'label' => 'permissions.groups.suppliers',
            'permissions' => ['suppliers.view', 'suppliers.manage'],
        ],
        'catalog' => [
            'label' => 'permissions.groups.catalog',
            'permissions' => ['products.view', 'products.manage'],
        ],
        'inventory' => [
            'label' => 'permissions.groups.inventory',
            'permissions' => ['inventory.view', 'inventory.manage'],
        ],
        'quotations' => [
            'label' => 'permissions.groups.quotations',
            'permissions' => ['quotations.view', 'quotations.manage'],
        ],
        'invoices' => [
            'label' => 'permissions.groups.invoices',
            'permissions' => ['invoices.view', 'invoices.manage'],
        ],
        'payments' => [
            'label' => 'permissions.groups.payments',
            'permissions' => ['payments.view', 'payments.manage'],
        ],
        'expenses' => [
            'label' => 'permissions.groups.expenses',
            'permissions' => ['expenses.view', 'expenses.manage'],
        ],
        'reports' => [
            'label' => 'permissions.groups.reports',
            'permissions' => ['reports.view'],
        ],
        'users' => [
            'label' => 'permissions.groups.users',
            'permissions' => ['users.view', 'users.manage'],
        ],
        'roles' => [
            'label' => 'permissions.groups.roles',
            'permissions' => ['roles.manage'],
        ],
        'activity' => [
            'label' => 'permissions.groups.activity',
            'permissions' => ['activity.view'],
        ],
        'settings' => [
            'label' => 'permissions.groups.settings',
            'permissions' => ['settings.manage'],
        ],
    ],
];
