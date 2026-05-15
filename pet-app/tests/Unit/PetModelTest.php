<?php

namespace Tests\Unit;

use App\Models\Pet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_pet_has_correct_fillable_fields(): void
    {
        $fillable = (new Pet)->getFillable();

        $this->assertEqualsCanonicalizing([
            'name', 'type', 'breed', 'age', 'description', 'image_path'
        ], $fillable);
    }

    public function test_pet_can_be_created(): void
    {
        $pet = Pet::create([
            'name'        => 'Max',
            'type'        => 'Dog',
            'breed'       => 'Golden Retriever',
            'age'         => 3,
            'description' => 'Friendly dog',
            'image_path'  => null,
        ]);

        $this->assertDatabaseHas('pets', ['name' => 'Max', 'type' => 'Dog']); // row in DB
        $this->assertInstanceOf(Pet::class, $pet);   // correct object type returned
    }

    public function test_pet_breed_and_image_are_nullable(): void
    {
        $pet = Pet::create([
            'name'        => 'Buddy',
            'type'        => 'Dog',
            'age'         => 2,
            'description' => 'Good boy',
        ]);

        $this->assertNull($pet->breed);
        $this->assertNull($pet->image_path);
    }

    public function test_pet_can_be_updated(): void
    {
        $pet = Pet::create([
            'name' => 'Old Name', 'type' => 'Cat',
            'age' => 1, 'description' => 'desc',
        ]);

        $pet->update(['name' => 'New Name', 'age' => 5]);

        $this->assertDatabaseHas('pets', ['id' => $pet->id, 'name' => 'New Name', 'age' => 5]); // row in DB with updated values
    }

    public function test_pet_can_be_deleted(): void
    {
        $pet = Pet::create([
            'name' => 'Temp', 'type' => 'Bird',
            'age' => 1, 'description' => 'desc',
        ]);

        $pet->delete();

        $this->assertDatabaseMissing('pets', ['id' => $pet->id]);
    }
}