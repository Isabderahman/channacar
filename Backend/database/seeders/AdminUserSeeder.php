<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the application's default admin account.
     */
    public function run(): void
    {
        $name = env('ADMIN_USER_NAME', 'Admin');
        $email = env('ADMIN_USER_EMAIL', 'admin@channacar.com');
        $password = env('ADMIN_USER_PASSWORD', 'Admin12345!');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'role' => 'admin',
            ],
        );
    }
}
