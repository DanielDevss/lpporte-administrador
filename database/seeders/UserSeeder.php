<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $isProduction = config('app.debug') == true;
        $email = $isProduction ? 'admin@lpporte.com' : 'admin@example.com';
        $password = $isProduction
            ? Hash::make('lpporte.2025')
            : Hash::make('12345678');

        User::updateOrCreate(
            [
                'email' => $email
            ],
            [
                "email" => $email,
                "password" => $password,
                "name" => "Luis Angel Pineda"
            ]
        );

        if ($isProduction) {
            User::updateOrCreate([
                'email' => 'luisdaniel.dlr.380@gmail.com',
                'password' => Hash::make('Dan.2001'),
                'name' => 'Luis Daniel de la Rosa'
            ]);
        }
    }
}
