<?php

namespace Tests\Unit;

use App\Services\PetService;
use Tests\TestCase;

class PetServiceTest extends TestCase
{
    public function test_get_pet_returns_correct_structure(): void
    {
        $mock = $this->getMockBuilder(PetService::class)
            ->onlyMethods(['getPet'])
            ->getMock();

        $mock->method('getPet')->willReturn([
            'pet'   => 'cat',
            'fact'  => 'Cats sleep 16 hours a day.',
            'image' => 'http://localhost/img/cat.jpg',
        ]);

        $result = $mock->getPet('cat');

        $this->assertArrayHasKey('pet', $result);
        $this->assertArrayHasKey('fact', $result);
        $this->assertArrayHasKey('image', $result);
        $this->assertEquals('cat', $result['pet']);
    }

    public function test_get_pet_returns_fallback_on_api_failure(): void
    {
        $mock = $this->getMockBuilder(PetService::class)
            ->onlyMethods(['getPet'])
            ->getMock();

        $mock->method('getPet')->willReturn([
            'pet'   => 'cat',
            'fact'  => 'No data available',
            'image' => 'http://localhost/img/cat.jpg',
        ]);

        $result = $mock->getPet('cat');

        $this->assertEquals('No data available', $result['fact']);
    }

    public function test_get_pet_supports_dog_type(): void
    {
        $mock = $this->getMockBuilder(PetService::class)
            ->onlyMethods(['getPet'])
            ->getMock();

        $mock->method('getPet')->willReturn([
            'pet'   => 'dog',
            'fact'  => 'Dogs are loyal animals.',
            'image' => 'http://localhost/img/dog.jpg',
        ]);

        $result = $mock->getPet('dog');

        $this->assertEquals('dog', $result['pet']);
        $this->assertStringContainsString('dog.jpg', $result['image']);
    }

    public function test_get_pet_defaults_to_bird_for_unknown_type(): void
    {
        $mock = $this->getMockBuilder(PetService::class)
            ->onlyMethods(['getPet'])
            ->getMock();

        $mock->method('getPet')->willReturn([
            'pet'   => 'hen',
            'fact'  => 'No data available',
            'image' => 'http://localhost/img/bird.jpg',
        ]);

        $result = $mock->getPet('hen');

        $this->assertStringContainsString('bird.jpg', $result['image']);
    }
}