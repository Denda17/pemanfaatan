<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@pemanfaatan.jabarprov.go.id'],
            [
                'name' => 'Admin UPTD Pengamanan & Pemanfaatan Aset',
                'role' => 'admin',
                'phone' => '081234567890',
                'instansi' => 'UPTD Pengamanan dan Pemanfaatan Aset Pemprov Jawa Barat',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            BookingRequirementSeeder::class,
            BookingSeeder::class,
            RegulationSeeder::class,
        ]);
    }
}
