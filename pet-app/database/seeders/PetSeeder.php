<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Pet::create([
            'name' => 'Max',
            'type' => 'Dog',
            'breed' => 'Golden Retriever',
            'age' => 3,
            'description' => 'A very friendly dog.',
            'image_path' => null,
        ]);

        \App\Models\Pet::create([
            'name' => 'Bella',
            'type' => 'Cat',
            'breed' => 'Siamese',
            'age' => 2,
            'description' => 'Loves to sleep all day.',
            'image_path' => null,
        ]);
    }
}
