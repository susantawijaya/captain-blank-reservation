<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => env('MASTER_ADMIN_EMAIL', 'admin@captainblank.com')],
            [
                'name' => env('MASTER_ADMIN_NAME', 'Master Admin'),
                'password' => env('MASTER_ADMIN_PASSWORD', 'admin12345'),
                'role' => 'admin',
                'is_master_admin' => true,
                'phone' => env('MASTER_ADMIN_PHONE', '081234567890'),
                'address' => env('MASTER_ADMIN_ADDRESS', 'Kantor Captain Blank'),
                'email_verified_at' => now(),
            ],
        );

        $this->call(WebsiteContentSeeder::class);
    }
}
