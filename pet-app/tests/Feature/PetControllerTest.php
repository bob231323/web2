<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Services\PetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PetControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------
    // INDEX
    // -------------------------------------------------------

    public function test_index_returns_view_with_pets(): void
    {
        Pet::create(['name' => 'Max', 'type' => 'Dog', 'age' => 3, 'description' => 'Good boy']);

        $response = $this->get('/pets');

        $response->assertStatus(200);
        $response->assertViewIs('pets.index');
        $response->assertViewHas('pets');
    }

    public function test_index_returns_json_for_ajax_request(): void
    {
        Pet::create(['name' => 'Max', 'type' => 'Dog', 'age' => 3, 'description' => 'Good boy']);

        $response = $this->getJson('/pets');

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'count', 'pets']);
    }

    public function test_index_filters_by_search(): void
    {
        Pet::create(['name' => 'Max',   'type' => 'Dog', 'age' => 3, 'description' => 'Good boy']);
        Pet::create(['name' => 'Bella', 'type' => 'Cat', 'age' => 2, 'description' => 'Sleepy']);

        $response = $this->getJson('/pets?search=Max');

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Max']);

        $data = $response->json('pets');
        $this->assertCount(1, $data);
    }

    public function test_index_filters_by_type(): void
    {
        Pet::create(['name' => 'Max',   'type' => 'Dog', 'age' => 3, 'description' => 'Good boy']);
        Pet::create(['name' => 'Bella', 'type' => 'Cat', 'age' => 2, 'description' => 'Sleepy']);

        $response = $this->getJson('/pets?type=Cat');

        $response->assertStatus(200);
        $data = $response->json('pets');
        $this->assertCount(1, $data);
        $this->assertEquals('Cat', $data[0]['type']);
    }

    // -------------------------------------------------------
    // STORE
    // -------------------------------------------------------

    public function test_store_creates_pet_successfully(): void
    {
        Storage::fake('public');

        $response = $this->postJson('/pets', [
            'name'        => 'Rocky',
            'type'        => 'Dog',
            'breed'       => 'Bulldog',
            'age'         => 4,
            'description' => 'Strong and calm',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
        $this->assertDatabaseHas('pets', ['name' => 'Rocky']);
    }

    public function test_store_uploads_image(): void
{
    Storage::fake('public');


    $response = $this->postJson('/pets', [
        'name'        => 'Rocky',
        'type'        => 'Dog',
        'age'         => 4,
        'description' => 'Strong',
        'image'       => UploadedFile::fake()->image('pet.jpg'),
    ]);

    $response->assertStatus(200);

    $pet = Pet::where('name', 'Rocky')->first();
    $this->assertNotNull($pet->image_path);
    Storage::disk('public')->assertExists($pet->image_path); 
}

    public function test_store_fails_validation_with_missing_required_fields(): void
    {
        $response = $this->postJson('/pets', ['name' => 'Rocky']); // missing type, age, description

        $response->assertStatus(422);   // Unprocessable Entity
        $response->assertJsonFragment(['success' => false]);
        $response->assertJsonStructure(['errors']);
    }

    public function test_store_fails_with_negative_age(): void
    {
        $response = $this->postJson('/pets', [
            'name'        => 'Rocky',
            'type'        => 'Dog',
            'age'         => -1,
            'description' => 'desc',
        ]);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------

    public function test_update_modifies_pet_successfully(): void
    {
        Storage::fake('public');

        $pet = Pet::create(['name' => 'Old', 'type' => 'Cat', 'age' => 1, 'description' => 'desc']);

        $response = $this->putJson("/pets/{$pet->id}", [
            'name'        => 'New Name',
            'type'        => 'Cat',
            'age'         => 3,
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
        $this->assertDatabaseHas('pets', ['id' => $pet->id, 'name' => 'New Name']);
    }

public function test_update_replaces_old_image(): void
{
    Storage::fake('public');

    // Put file directly on fake disk instead of using UploadedFile->store()
    $oldPath = 'pets/old.jpg';
    Storage::disk('public')->put($oldPath, 'dummy content');

    $pet = Pet::create([
        'name' => 'Max',
         'type' => 'Dog',
        'age' => 2, 
        'description' => 'desc',
        'image_path' => $oldPath,
    ]);

    // $newImage = UploadedFile::fake()->create('new.jpg', 100, 'image/jpeg');

    $this->putJson("/pets/{$pet->id}", [
        'name'        => 'Max',
        'type'        => 'Dog',
        'age'         => 2,
        'description' => 'desc',
        'image'       => UploadedFile::fake()->image('new.jpg'),
        // 'image'       => $newImage,
    ]);

    Storage::disk('public')->assertMissing($oldPath);  
    // Verify new image was saved
    $pet->refresh();
    Storage::disk('public')->assertExists($pet->image_path);
}

    public function test_update_fails_validation(): void
    {
        $pet = Pet::create(['name' => 'Max', 'type' => 'Dog', 'age' => 2, 'description' => 'desc']);

        $response = $this->putJson("/pets/{$pet->id}", ['name' => '']); // empty required field

        $response->assertStatus(422);
    }

    // -------------------------------------------------------
    // DESTROY
    // -------------------------------------------------------

    public function test_destroy_deletes_pet(): void
    {
        $pet = Pet::create(['name' => 'Temp', 'type' => 'Bird', 'age' => 1, 'description' => 'desc']);

        $response = $this->deleteJson("/pets/{$pet->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
        $this->assertDatabaseMissing('pets', ['id' => $pet->id]);  // row no longer in DB
    }

public function test_destroy_removes_image_from_storage(): void
{
    Storage::fake('public');

    // Put file directly on fake disk
    $path = 'pets/pet.jpg';
    Storage::disk('public')->put($path, 'dummy content');

    $pet = Pet::create([
        'name' => 'Temp',
         'type' => 'Bird',
        'age' => 1, 
        'description' => 'desc',
        'image_path' => $path,
    ]);

    $response = $this->deleteJson("/pets/{$pet->id}");

    $response->assertStatus(200);

    Storage::disk('public')->assertMissing($path);
    $this->assertDatabaseMissing('pets', ['id' => $pet->id]);

}

    // -------------------------------------------------------
    // GET PET (external API route)
    // -------------------------------------------------------

    public function test_get_pet_returns_json_response(): void
    {
        $this->mock(PetService::class, function ($mock) {
            $mock->shouldReceive('getPet')
                 ->once()
                 ->with('cat')
                 ->andReturn([
                     'pet'   => 'cat',
                     'fact'  => 'Cats sleep 16 hours.',
                     'image' => 'http://localhost/img/cat.jpg',
                 ]);
        });

        $response = $this->getJson('/api/pet?pet=cat');

        $response->assertStatus(200);
        $response->assertJsonStructure(['pet', 'fact', 'image']);
        $response->assertJsonFragment(['pet' => 'cat']);
    }

    public function test_get_pet_defaults_to_cat(): void
    {
        $this->mock(PetService::class, function ($mock) {
            $mock->shouldReceive('getPet')
                 ->once()
                 ->with('cat')
                 ->andReturn([
                     'pet'   => 'cat',
                     'fact'  => 'Some fact',
                     'image' => 'http://localhost/img/cat.jpg',
                 ]);
        });

        $response = $this->getJson('/api/pet'); // no ?pet= param

        $response->assertStatus(200);
        $response->assertJsonFragment(['pet' => 'cat']);
    }
}