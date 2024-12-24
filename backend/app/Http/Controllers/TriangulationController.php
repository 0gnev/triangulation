<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TriangulationController extends Controller
{
    /**
     * Handle the triangulation request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function triangulate(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $newPoint = [
            'lat' => $request->input('latitude'),
            'lng' => $request->input('longitude'),
        ];

        $referencePoints = [
            ['name' => 'A', 'lat' => 50.110889, 'lng' => 8.682139],
            ['name' => 'B', 'lat' => 39.048111, 'lng' => -77.472806],
            ['name' => 'C', 'lat' => 45.849100, 'lng' => -119.714000],
        ];

        $distances = [];
        foreach ($referencePoints as $point) {
            $distance = $this->calculateDistance(
                $newPoint['lat'],
                $newPoint['lng'],
                $point['lat'],
                $point['lng']
            );
            $distances[] = [
                'reference_point' => $point['name'],
                'distance_km' => round($distance, 3),
            ];
        }

        return response()->json(['success' => true, 'distances' => $distances]);
    }

    /**
     * Calculate the great-circle distance between two points.
     *
     * @param float $lat1 Latitude of point 1
     * @param float $lng1 Longitude of point 1
     * @param float $lat2 Latitude of point 2
     * @param float $lng2 Longitude of point 2
     * @return float Distance in kilometers
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Radius of the Earth in km

        $lat1Rad = deg2rad($lat1);
        $lng1Rad = deg2rad($lng1);
        $lat2Rad = deg2rad($lat2);
        $lng2Rad = deg2rad($lng2);

        // Haversine formula
        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLng = $lng2Rad - $lng1Rad;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLng / 2) * sin($deltaLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
