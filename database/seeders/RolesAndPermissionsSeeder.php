<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * The columns rendered on the Roles screen, in display order. Not every
     * module uses every action — a module simply omits the ones that don't
     * apply to it (see MODULES below).
     */
    public const ACTIONS = ['view', 'create', 'edit', 'delete', 'manage'];

    /**
     * Every module the app understands, and which of the actions above apply
     * to it, mapped to the concrete permission string. This is the single
     * source of truth for permission names, and is reused by the Roles UI
     * to render the checkbox/badge matrix.
     */
    public const MODULES = [
        'Products' => [
            'view' => 'view products',
            'create' => 'create products',
            'edit' => 'edit products',
            'delete' => 'delete products',
            'manage' => 'manage products', // stock adjustments (restock/damage/correction)
        ],
        'Categories' => [
            'view' => 'view categories',
            'create' => 'create categories',
            'edit' => 'edit categories',
            'delete' => 'delete categories',
        ],
        'POS' => [
            'manage' => 'access pos', // use the terminal and complete sales
        ],
        'Sales' => [
            'view' => 'view sales',
            'delete' => 'void sales',
        ],
        'Customers' => [
            'view' => 'view customers',
            'create' => 'create customers',
            'edit' => 'edit customers',
            'delete' => 'delete customers',
        ],
        'Reports' => [
            'view' => 'view reports',
        ],
        'Settings' => [
            'manage' => 'manage settings',
        ],
        'Users' => [
            'view' => 'view users',
            'create' => 'create users',
            'edit' => 'edit users',
            'delete' => 'delete users',
            'manage' => 'manage users', // activate/deactivate
        ],
        'Roles' => [
            'view' => 'view roles',
            'manage' => 'manage roles',
        ],
    ];

    public const BUILT_IN_ROLES = ['Super Admin', 'Cashier'];

    /**
     * The permission set each built-in role is seeded with, and restored to
     * by "Reset to Default". Super Admin always gets every permission.
     */
    private const DEFAULT_ROLE_PERMISSIONS = [
        'Cashier' => [
            'access pos',
            'view sales',
            'view products',
            'view customers',
            'create customers',
            'edit customers',
        ],
    ];

    /**
     * Every permission string the app defines, flattened.
     *
     * @return string[]
     */
    public static function allPermissions(): array
    {
        return collect(self::MODULES)->flatMap(fn ($actions) => array_values($actions))->all();
    }

    /**
     * The default permission set for a built-in role, or null if the role
     * isn't built-in (custom roles have no "default" to reset to).
     *
     * @return string[]|null
     */
    public static function defaultPermissionsFor(string $roleName): ?array
    {
        if ($roleName === 'Super Admin') {
            return self::allPermissions();
        }

        return self::DEFAULT_ROLE_PERMISSIONS[$roleName] ?? null;
    }

    /**
     * Seed permissions and the default roles. Safe to run repeatedly.
     */
    public function run(): void
    {
        foreach (self::allPermissions() as $permission) {
            Permission::findOrCreate($permission);
        }

        foreach (self::BUILT_IN_ROLES as $roleName) {
            $role = Role::findOrCreate($roleName);
            $role->syncPermissions(self::defaultPermissionsFor($roleName));
        }
    }
}
