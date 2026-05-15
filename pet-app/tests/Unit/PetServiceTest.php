<?php

namespace Tests\Unit;

use App\Services\PetService;
use Tests\TestCase;

class PetServiceTest extends TestCase
{
    public function test_get_pet_returns_correct_structure_for_cat(): void
    {
        // Mock only callAPI, let real getPet() run
        $service = $this->getMockBuilder(PetService::class)
            ->onlyMethods(['callAPI'])
            ->getMock();

        $service->method('callAPI')->willReturn([
            'fact' => 'Cats sleep 16 hours a day.'
        ]);

        $result = $service->getPet('cat'); // ← real getPet() runs now

        $this->assertArrayHasKey('pet', $result);
        $this->assertArrayHasKey('fact', $result);
        $this->assertArrayHasKey('image', $result);
        $this->assertEquals('cat', $result['pet']);
        $this->assertEquals('Cats sleep 16 hours a day.', $result['fact']);
    }

    public function test_get_pet_returns_correct_fact_for_dog(): void
    {
        $service = $this->getMockBuilder(PetService::class)
            ->onlyMethods(['callAPI'])
            ->getMock();

        // Dog API returns a different structure — your real switch handles this
        $service->method('callAPI')->willReturn([
            'data' => [
                ['attributes' => ['body' => 'Dogs are loyal animals.']]
            ]
        ]);

        $result = $service->getPet('dog'); // ← real switch case 'dog' runs

        $this->assertEquals('dog', $result['pet']);
        $this->assertEquals('Dogs are loyal animals.', $result['fact']);
        $this->assertStringContainsString('dog.jpg', $result['image']);
    }

    public function test_get_pet_defaults_to_bird_for_unknown_type(): void
    {
        $service = $this->getMockBuilder(PetService::class)
            ->onlyMethods(['callAPI'])
            ->getMock();

        $service->method('callAPI')->willReturn([
            'fact' => 'Birds can fly.'
        ]);

        $result = $service->getPet('hen'); // ← real default case runs

        $this->assertEquals('hen', $result['pet']);
        $this->assertEquals('Birds can fly.', $result['fact']);
        $this->assertStringContainsString('bird.jpg', $result['image']);
    }

    public function test_get_pet_returns_fallback_when_api_fails(): void
    {
        $service = $this->getMockBuilder(PetService::class)
            ->onlyMethods(['callAPI'])
            ->getMock();

        // Simulate cURL failure — callAPI returns null
        $service->method('callAPI')->willReturn(null);

        $result = $service->getPet('cat'); // ← real getPet() handles null response

        $this->assertEquals('No data available', $result['fact']);
    }

    public function test_get_pet_returns_fallback_when_api_returns_wrong_structure(): void
    {
        $service = $this->getMockBuilder(PetService::class)
            ->onlyMethods(['callAPI'])
            ->getMock();

        // API returns something unexpected — missing 'fact' key
        $service->method('callAPI')->willReturn(['unexpected_key' => 'value']);

        $result = $service->getPet('cat'); // ← real isset($res['fact']) check runs

        $this->assertEquals('No data available', $result['fact']);
    }
}
