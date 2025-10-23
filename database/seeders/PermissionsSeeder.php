<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'users.index',
            'users.show',
            'users.create',
            'users.edit',
            'users.destroy',
            'users.import',
            'users.export',
            'roles.index',
            'roles.show',
            'roles.create',
            'roles.edit',
            'roles.destroy',
            'roles.import',
            'roles.export',
            'permissions.index',
            'permissions.show',
            'permissions.create',
            'permissions.edit',
            'permissions.destroy',
            'permissions.import',
            'permissions.export',
            'api.sanctum',
            'api.passport',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }
    }
}
