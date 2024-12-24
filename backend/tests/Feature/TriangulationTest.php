<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TriangulationTest extends TestCase
{
    /**
     * Test successful triangulation.
     */
    public function test_successful_triangulation()
    {
        $payload = [
            'latitude' => 40.712776,   // Example: New York City
            'longitude' => -74.005974,
        ];

        $response = $this->postJson('/api/triangulate', $payload);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'distances' => [
                    '*' => [
                        'reference_point',
                        'distance_km',
                    ],
                ],
            ])
            ->assertJson(['success' => true]);

        // Verify there are three distance results
        $data = $response->json();
        $this->assertCount(3, $data['distances']);
    }

    /**
     * Test triangulation with missing latitude and longitude.
     */
    public function test_triangulation_with_missing_fields()
    {
        $payload = []; // No latitude or longitude provided

        $response = $this->postJson('/api/triangulate', $payload);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    /**
     * Test triangulation with latitude and longitude out of range.
     */
    public function test_triangulation_with_out_of_range_values()
    {
        $payload = [
            'latitude' => 95,  // Invalid: beyond -90 to 90
            'longitude' => -190, // Invalid: beyond -180 to 180
        ];

        $response = $this->postJson('/api/triangulate', $payload);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    /**
     * Test triangulation with invalid data types.
     */
    public function test_triangulation_with_invalid_data_types()
    {
        $payload = [
            'latitude' => 'not-a-number',
            'longitude' => 'not-a-number',
        ];

        $response = $this->postJson('/api/triangulate', $payload);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    /**
     * Test triangulation with edge case values for latitude and longitude.
     */
    public function test_triangulation_with_edge_case_values()
    {
        $payload = [
            'latitude' => -90,  // Minimum valid latitude
            'longitude' => 180, // Maximum valid longitude
        ];

        $response = $this->postJson('/api/triangulate', $payload);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'distances' => [
                    '*' => [
                        'reference_point',
                        'distance_km',
                    ],
                ],
            ]);

        $data = $response->json();
        $this->assertCount(3, $data['distances']);
    }

    /**
     * Test triangulation with extra unnecessary fields.
     */
    public function test_triangulation_with_extra_fields()
    {
        $payload = [
            'latitude' => 40.712776,
            'longitude' => -74.005974,
            'extra_field' => 'should-be-ignored',
        ];

        $response = $this->postJson('/api/triangulate', $payload);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'distances' => [
                    '*' => [
                        'reference_point',
                        'distance_km',
                    ],
                ],
            ]);
    }
}
