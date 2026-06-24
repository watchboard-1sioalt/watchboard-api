<?php

namespace Database\Seeders;

use App\Models\Utilisateurs;
use App\Models\Tags;
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
        // User::factory(10)->create();

        // User::create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);


        Utilisateurs::create([
            'nom' => 'Nom',
            'prenom' => 'Prenom',
            'email'=> 'monemail@email.com',
            'password' => bcrypt('monpass'),
        ]);

        Tags::create(
            [
            'tag' => 'Vidéo',
            'public' => true,
            ], 
            [
            'tag' => 'Article',
            'public' => true,
            ]
        );
    }
}
