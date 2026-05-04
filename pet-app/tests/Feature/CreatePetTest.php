<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Pet;

class CreatePetTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_pet_with_valid_data()
    {
        $response = $this->post(route('pets.store'), [
            'name'  => 'Buddy',
            'type'  => 'Dog',
            'breed' => 'Labrador',
            'age'   => 3,
        ]);

        $response->assertRedirect(route('pets.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pets', ['name' => 'Buddy']);
    }

    public function test_fails_when_name_is_missing()
    {
        $response = $this->post(route('pets.store'), [
            'name' => '',
            'type' => 'Cat',
            'age'  => 2,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_shows_all_pets_on_index_page()
    {
        Pet::create([
            'name' => 'Mimi',
            'type' => 'Cat',
            'age'  => 1,
        ]);

        $response = $this->get(route('pets.index'));
        $response->assertStatus(200);
        $response->assertSee('Mimi');
    }
}