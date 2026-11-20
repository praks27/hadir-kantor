<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@hadirkantor.test',
                'password' => 'password123',
                'nik' => 'SUPERADMIN-001',
                'position' => 'Administrator',
                'role' => 'superadmin',
                'is_active' => true,
            ],
        );

        $this->command?->info('Akun superadmin siap. Username: superadmin');
    }
}
