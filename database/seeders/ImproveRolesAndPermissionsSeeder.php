<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ImproveRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            ['name' => 'super_admin', 'guard_name' => 'web'],
            ['name' => 'manager', 'guard_name' => 'web'],
            ['name' => 'employee', 'guard_name' => 'web'],
            ['name' => 'guest', 'guard_name' => 'web'],
            ['name' => 'coach', 'guard_name' => 'web'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']],
                $roleData
            );
        }

        $permissionsByGroup = [
            'Utilisateurs' => [
                'show-users',
                'create-users',
                'edit-users',
                'delete-users',
                'manage-user-roles',
                'manage-user-permissions',
            ],
            'Roles et Permissions' => [
                'show-roles',
                'create-roles',
                'edit-roles',
                'delete-roles',
                'manage-permissions',
                'assign-roles',
                'revoke-roles',
            ],
            'Rapports' => [
                'view-reports',
                'generate-reports',
                'export-reports',
                'manage-reports',
            ],
            'Systeme' => [
                'system-settings',
                'system-backup',
                'system-logs',
                'system-maintenance',
            ],
            'Fitness' => [
                'show-participantes',
                'create-participantes',
                'edit-participantes',
                'delete-participantes',
                'view-participante-health-data',
                'show-challenges',
                'create-challenges',
                'edit-challenges',
                'delete-challenges',
                'show-inscriptions',
                'create-inscriptions',
                'edit-inscriptions',
                'delete-inscriptions',
                'change-inscription-status',
                'show-payments',
                'create-payments',
                'edit-payments',
                'delete-payments',
                'show-recus',
                'generate-recus',
                'record-attendance',
                'edit-attendance',
                'record-measurements',
                'edit-measurements',
                'delete-measurements',
                'manage-media',
                'delete-media',
                'manage-comments',
                'view-bilan',
                'export-bilan-pdf',
                'view-dashboard',
                'view-activity-log',
            ],
        ];

        foreach ($permissionsByGroup as $permissions) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission, 'guard_name' => 'web'],
                    ['name' => $permission, 'guard_name' => 'web']
                );
            }
        }

        $roleAssignments = [
            'super_admin' => array_keys($permissionsByGroup),
            'manager' => [
                'Utilisateurs',
                'Rapports',
                'Fitness',
            ],
            'employee' => [],
            'guest' => [],
        ];

        foreach ($roleAssignments as $roleName => $groups) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();

            if (! $role) {
                continue;
            }

            $permissions = [];

            foreach ($groups as $group) {
                $permissions = array_merge($permissions, $permissionsByGroup[$group]);
            }

            $role->syncPermissions($permissions);
        }

        Role::where('name', 'coach')
            ->where('guard_name', 'web')
            ->first()
            ?->syncPermissions([
                'show-participantes',
                'view-participante-health-data',
                'show-challenges',
                'show-inscriptions',
                'record-attendance',
                'edit-attendance',
                'record-measurements',
                'edit-measurements',
                'manage-media',
                'manage-comments',
                'view-bilan',
            ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('Roles et permissions ameliores avec succes!');
        $this->command->info('Structure des permissions creee par groupe');
        $this->command->info('Assignation hierarchique des permissions configuree');
    }
}
