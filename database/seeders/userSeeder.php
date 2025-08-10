<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class userSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'admin1',
            'email' => 'admin@teste.com',
            'password' => 'senha123',
        ]);
    }
}
