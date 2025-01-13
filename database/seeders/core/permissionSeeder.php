<?php

namespace Database\Seeders\core;

use App\Models\Core\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class permissionSeeder extends Seeder
{

    public function run(): void
    {

        $role = Role::create([
            'name' => 'super',
            'display_name' => 'Super Admin',
            'redirect_to' => '/',
            'guard_name' => 'sanctum',
        ]);

        $user = User::create([
            'name' => 'Linox',
            'username' => 'linox',
            'email' => 'lnx.dvlpr@gmail.com',
            'password' => 'password',
        ]);

        $user->assignRole($role);
    }
}
