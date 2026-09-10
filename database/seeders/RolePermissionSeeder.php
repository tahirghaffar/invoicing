<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // Business
            ['Business Profile View', 'business.profile.view', 'business'],
            ['Business Profile Update', 'business.profile.update', 'business'],

            // Users
            ['Users View', 'users.view', 'users'],
            ['Users Manage', 'users.manage', 'users'],

            // Customers
            ['Customers View', 'customers.view', 'customers'],
            ['Customers Create', 'customers.create', 'customers'],
            ['Customers Update', 'customers.update', 'customers'],
            ['Customers Delete', 'customers.delete', 'customers'],

            // Products
            ['Products View', 'products.view', 'products'],
            ['Products Create', 'products.create', 'products'],
            ['Products Update', 'products.update', 'products'],
            ['Products Delete', 'products.delete', 'products'],

            // Invoices
            ['Invoices View', 'invoices.view', 'invoices'],
            ['Invoices Create', 'invoices.create', 'invoices'],
            ['Invoices Update', 'invoices.update', 'invoices'],
            ['Invoices Delete', 'invoices.delete', 'invoices'],
            ['Invoices Submit FBR', 'invoices.submit_fbr', 'invoices'],

            // Reports
            ['Reports View', 'reports.view', 'reports'],

            // FBR
            ['FBR Settings View', 'fbr.settings.view', 'fbr'],
            ['FBR Settings Update', 'fbr.settings.update', 'fbr'],
        ];

        foreach ($permissions as [$name, $slug, $module]) {
            Permission::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'module' => $module,
                ]
            );
        }

        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'scope' => 'system',
                'description' => 'Full platform administration',
            ]
        );

        $businessAdmin = Role::updateOrCreate(
            ['slug' => 'business-admin'],
            [
                'name' => 'Business Admin',
                'scope' => 'business',
            ]
        );

        $manager = Role::updateOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'scope' => 'business',
            ]
        );

        $accountant = Role::updateOrCreate(
            ['slug' => 'accountant'],
            [
                'name' => 'Accountant',
                'scope' => 'business',
            ]
        );

        $operator = Role::updateOrCreate(
            ['slug' => 'invoice-operator'],
            [
                'name' => 'Invoice Operator',
                'scope' => 'business',
            ]
        );

        $viewer = Role::updateOrCreate(
            ['slug' => 'viewer'],
            [
                'name' => 'Viewer',
                'scope' => 'business',
            ]
        );

        $allPermissions = Permission::pluck('id');

        $superAdmin->permissions()->sync($allPermissions);
        $businessAdmin->permissions()->sync($allPermissions);

        $manager->permissions()->sync(
            Permission::whereIn('slug', [
                'business.profile.view',
                'users.view',
                'customers.view',
                'customers.create',
                'customers.update',
                'products.view',
                'products.create',
                'products.update',
                'invoices.view',
                'invoices.create',
                'invoices.update',
                'reports.view',
            ])->pluck('id')
        );

        $accountant->permissions()->sync(
            Permission::whereIn('slug', [
                'business.profile.view',
                'customers.view',
                'customers.create',
                'customers.update',
                'products.view',
                'invoices.view',
                'invoices.create',
                'invoices.update',
                'invoices.submit_fbr',
                'reports.view',
            ])->pluck('id')
        );

        $operator->permissions()->sync(
            Permission::whereIn('slug', [
                'customers.view',
                'products.view',
                'invoices.view',
                'invoices.create',
                'invoices.update',
            ])->pluck('id')
        );

        $viewer->permissions()->sync(
            Permission::whereIn('slug', [
                'customers.view',
                'products.view',
                'invoices.view',
                'reports.view',
            ])->pluck('id')
        );
    }
}
