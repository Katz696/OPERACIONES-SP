<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
class roles extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::create(['name' => 'admin']);
        $manager = Role::create(['name' => 'manager']);
        $user = Role::create(['name' => 'user']);

        Permission::create(['name' => 'projects.view']);
        Permission::create(['name' => 'projects.edit']);
        Permission::create(['name' => 'projects.delete']);
        Permission::create(['name' => 'users.view']);
        Permission::create(['name' => 'users.edit']);
        Permission::create(['name' => 'users.delete']);
        Permission::create(['name' => 'clients.view']);
        Permission::create(['name' => 'clients.edit']);
        Permission::create(['name' => 'clients.delete']);

        $admin->givePermissionTo(Permission::all());
        $manager->givePermissionTo(['projects.view', 'projects.edit', 'users.view', 'clients.view']);
        $user->givePermissionTo(['projects.view']);

        $user = User::first();
        $user->assignRole('admin');
    }
}
