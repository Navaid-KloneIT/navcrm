<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guardName = 'web';

        // Define modules and actions
        $modules = [
            'contacts', 'accounts', 'leads', 'users', 'roles', 'tags',
            'opportunities', 'products', 'price-books', 'quotes', 'forecasts', 'sales-targets',
            'projects', 'timesheets',
        ];
        $actions = ['view', 'create', 'update', 'delete'];

        // Create permissions for each module x action combination
        $allPermissions = [];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissionName = "{$action}-{$module}";
                $permission = Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => $guardName,
                ]);
                $allPermissions[] = $permission;
            }
        }

        // Create special permissions
        $specialPermissions = ['convert-leads', 'export-data', 'manage-settings', 'generate-quotes', 'manage-pipeline'];
        foreach ($specialPermissions as $permissionName) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $guardName,
            ]);
            $allPermissions[] = $permission;
        }

        // Create roles and assign permissions

        // Admin: all permissions
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => $guardName,
        ]);
        $adminRole->syncPermissions(Permission::where('guard_name', $guardName)->get());

        // Manager: CRM modules + export
        $managerRole = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => $guardName,
        ]);
        $managerPermissions = Permission::where('guard_name', $guardName)
            ->where(function ($query) {
                $query->where('name', 'like', '%-contacts')
                    ->orWhere('name', 'like', '%-accounts')
                    ->orWhere('name', 'like', '%-leads')
                    ->orWhere('name', 'like', '%-tags')
                    ->orWhere('name', 'like', '%-opportunities')
                    ->orWhere('name', 'like', '%-products')
                    ->orWhere('name', 'like', '%-price-books')
                    ->orWhere('name', 'like', '%-quotes')
                    ->orWhere('name', 'like', '%-forecasts')
                    ->orWhere('name', 'like', '%-sales-targets')
                    ->orWhere('name', 'convert-leads')
                    ->orWhere('name', 'export-data')
                    ->orWhere('name', 'generate-quotes')
                    ->orWhere('name', 'manage-pipeline')
                    ->orWhere('name', 'like', '%-projects')
                    ->orWhere('name', 'like', '%-timesheets');
            })
            ->get();
        $managerRole->syncPermissions($managerPermissions);

        // Sales: CRM operations (view, create, update on contacts/accounts/leads/tags + convert)
        $salesRole = Role::firstOrCreate([
            'name' => 'sales',
            'guard_name' => $guardName,
        ]);
        $salesPermissions = Permission::where('guard_name', $guardName)
            ->where(function ($query) {
                $query->whereIn('name', [
                    'view-contacts', 'create-contacts', 'update-contacts', 'delete-contacts',
                    'view-accounts', 'create-accounts', 'update-accounts', 'delete-accounts',
                    'view-leads', 'create-leads', 'update-leads', 'delete-leads',
                    'view-tags', 'create-tags', 'update-tags', 'delete-tags',
                    'view-opportunities', 'create-opportunities', 'update-opportunities', 'delete-opportunities',
                    'view-products',
                    'view-quotes', 'create-quotes', 'update-quotes',
                    'view-forecasts',
                    'view-sales-targets',
                    'view-projects',
                    'view-timesheets',
                    'convert-leads',
                    'generate-quotes',
                ]);
            })
            ->get();
        $salesRole->syncPermissions($salesPermissions);

        // Viewer: read-only
        $viewerRole = Role::firstOrCreate([
            'name' => 'viewer',
            'guard_name' => $guardName,
        ]);
        $viewerPermissions = Permission::where('guard_name', $guardName)
            ->where('name', 'like', 'view-%')
            ->get();
        $viewerRole->syncPermissions($viewerPermissions);
    }
}
