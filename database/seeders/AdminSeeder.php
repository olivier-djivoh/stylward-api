<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@saas-stylistes.com'],
            [
                'first_name'        => 'Admin',
                'last_name'         => 'SaaS',
                'phone'             => null,
                'password'          => Hash::make('AdminMotDePasse123!'),
                'role'              => 'admin',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Compte admin créé : admin@saas-stylistes.com / AdminMotDePasse123!');
    }
}
