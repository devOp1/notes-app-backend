<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'users.view',
            'users.update',
            'users.delete',
            'users.ban',
            'users.revoke-tokens',
            'users.verify',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super-admin', 'web');
        $superAdmin->givePermissionTo($permissions);

        $support = Role::findOrCreate('support', 'web');
        $support->givePermissionTo(['users.view', 'users.verify', 'users.revoke-tokens']);

        // Grant existing admins the super-admin role so nobody loses access
        // when this feature is rolled out.
        Admin::all()->each(function (Admin $admin) use ($superAdmin) {
            if (! $admin->hasRole($superAdmin)) {
                $admin->assignRole($superAdmin);
            }
        });
    }
}
