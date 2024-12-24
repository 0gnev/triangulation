<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TriangulationControllerTest extends TestCase
{
    private const COORDINATE_TOLERANCE = 0.2; // 0.2 degrees tolerance for coordinates

    private function calculateGreatCircleDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371;
        $lat1Rad = deg2rad($lat1);
        $lng1Rad = deg2rad($lng1);
        $lat2Rad = deg2rad($lat2);
        $lng2Rad = deg2rad($lng2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLng = $lng2Rad - $lng1Rad;

        $a = sin($deltaLat/2) * sin($deltaLat/2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLng/2) * sin($deltaLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    public function testSuccessfulTrilateration()
    {

        $referencePoints = [
            'A' => ['lat' => 40.7128, 'lng' => -74.0060], // New York
            'B' => ['lat' => 42.3601, 'lng' => -71.0589], // Boston
            'C' => ['lat' => 39.9526, 'lng' => -75.1652], // Philadelphia
        ];


        $targetPoint = ['lat' => 41.0, 'lng' => -73.0];


        $distances = [
            'distanceA' => $this->calculateGreatCircleDistance(
                $targetPoint['lat'], $targetPoint['lng'],
                $referencePoints['A']['lat'], $referencePoints['A']['lng']
            ),
            'distanceB' => $this->calculateGreatCircleDistance(
                $targetPoint['lat'], $targetPoint['lng'],
                $referencePoints['B']['lat'], $referencePoints['B']['lng']
            ),
            'distanceC' => $this->calculateGreatCircleDistance(
                $targetPoint['lat'], $targetPoint['lng'],
                $referencePoints['C']['lat'], $referencePoints['C']['lng']
            ),
        ];


        $distances = array_map(function($d) {
            return $d + (rand(-10, 10) / 100); // Add ±0.1km random error
        }, $distances);

        $response = $this->postJson('/api/triangulate', array_merge(
            $distances,
            [
                'referenceA' => $referencePoints['A'],
                'referenceB' => $referencePoints['B'],
                'referenceC' => $referencePoints['C'],
            ]
        ));

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $result = $response->json();


        $this->assertArrayHasKey('coordinates', $result);
        $this->assertEqualsWithDelta(
            $targetPoint['lat'],
            $result['coordinates']['lat'],
            self::COORDINATE_TOLERANCE,
            "Latitude outside acceptable range"
        );
        $this->assertEqualsWithDelta(
            $targetPoint['lng'],
            $result['coordinates']['lng'],
            self::COORDINATE_TOLERANCE,
            "Longitude outside acceptable range"
        );
    }

    public function testInvalidTriangleInequalities()
    {

        $response = $this->postJson('/api/triangulate', [
            'distanceA' => 100,
            'distanceB' => 100,
            'distanceC' => 10000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'The entered distances do not satisfy the triangle inequalities based on the reference points\' positions. No such point exists.'
            ]);
    }

    public function testMissingParameters()
    {
        $response = $this->postJson('/api/triangulate', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['distanceA', 'distanceB', 'distanceC']);
    }

    public function testNonNumericDistances()
    {

        $response = $this->postJson('/api/triangulate', [
            'distanceA' => 'invalid',
            'distanceB' => 'invalid',
            'distanceC' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['distanceA', 'distanceB', 'distanceC']);
    }
}
