<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Pedro Raposo',
            'email' => 'pedro@raposo.com',
            'password' => bcrypt('12345678'),
            'email_verified_at' => date('c')
        ]);
    }
}
