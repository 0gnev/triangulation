<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TriangulationController extends Controller
{
    private const EPSILON = 1e-10;  // Small number for floating-point comparisons
    private const DISTANCE_TOLERANCE = 0.1;  // 100 meter tolerance for distance calculations

    public function triangulate(Request $request)
    {

        $request->validate([
            'distanceA' => 'required|numeric|min:0',
            'distanceB' => 'required|numeric|min:0',
            'distanceC' => 'required|numeric|min:0',
            'referenceA' => 'sometimes|array',
            'referenceA.lat' => 'required_with:referenceA|numeric',
            'referenceA.lng' => 'required_with:referenceA|numeric',
            'referenceB' => 'sometimes|array',
            'referenceB.lat' => 'required_with:referenceB|numeric',
            'referenceB.lng' => 'required_with:referenceB|numeric',
            'referenceC' => 'sometimes|array',
            'referenceC.lat' => 'required_with:referenceC|numeric',
            'referenceC.lng' => 'required_with:referenceC|numeric',
        ]);

        $referencePoints = [
            'A' => $request->input('referenceA', ['lat' => 50.110889, 'lng' => 8.682139]),
            'B' => $request->input('referenceB', ['lat' => 39.048111, 'lng' => -77.472806]),
            'C' => $request->input('referenceC', ['lat' => 45.849100, 'lng' => -119.714000]),
        ];

        $distances = [
            'A' => $request->input('distanceA'),
            'B' => $request->input('distanceB'),
            'C' => $request->input('distanceC'),
        ];

        if (!$this->checkTriangleInequalities($referencePoints, $distances)) {
            return response()->json([
                'success' => false,
                'message' => 'The entered distances do not satisfy the triangle inequalities based on the reference points\' positions. No such point exists.',
            ]);
        }

        $result = $this->performTrilateration($referencePoints, $distances);
        return response()->json($result);
    }

    private function checkTriangleInequalities($referencePoints, $distances)
    {
        $dAB = $this->calculateDistance(
            $referencePoints['A']['lat'], $referencePoints['A']['lng'],
            $referencePoints['B']['lat'], $referencePoints['B']['lng']
        );
        $dBC = $this->calculateDistance(
            $referencePoints['B']['lat'], $referencePoints['B']['lng'],
            $referencePoints['C']['lat'], $referencePoints['C']['lng']
        );
        $dAC = $this->calculateDistance(
            $referencePoints['A']['lat'], $referencePoints['A']['lng'],
            $referencePoints['C']['lat'], $referencePoints['C']['lng']
        );

        $tolerance = self::DISTANCE_TOLERANCE;

        return (
            $distances['A'] + $distances['B'] >= ($dAB - $tolerance) &&
            $distances['B'] + $distances['C'] >= ($dBC - $tolerance) &&
            $distances['A'] + $distances['C'] >= ($dAC - $tolerance) &&
            abs($distances['A'] - $distances['B']) <= ($dAB + $tolerance) &&
            abs($distances['B'] - $distances['C']) <= ($dBC + $tolerance) &&
            abs($distances['A'] - $distances['C']) <= ($dAC + $tolerance)
        );
    }

    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // kilometers

        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);

        $deltaLat = $lat2 - $lat1;
        $deltaLng = $lng2 - $lng1;

        $a = sin($deltaLat/2) * sin($deltaLat/2) +
             cos($lat1) * cos($lat2) *
             sin($deltaLng/2) * sin($deltaLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    private function performTrilateration($referencePoints, $distances)
    {

        $cartesianPoints = [];
        foreach ($referencePoints as $key => $point) {
            $cartesianPoints[$key] = $this->latLngToCartesian($point['lat'], $point['lng']);
        }

        $P1 = $cartesianPoints['A'];
        $P2 = $cartesianPoints['B'];
        $P3 = $cartesianPoints['C'];
        $r1 = $distances['A'];
        $r2 = $distances['B'];
        $r3 = $distances['C'];

        try {
            $results = $this->trilaterationSolver($P1, $P2, $P3, $r1, $r2, $r3);

            if ($results === null) {
                return [
                    'success' => false,
                    'message' => 'No valid trilateration solution exists with the given distances.',
                ];
            }

            $coordinates = [];

            foreach ($results as $result) {
                $latLng = $this->cartesianToLatLng($result);
                $coordinates[] = [
                    'latitude' => round($latLng['lat'], 6),
                    'longitude' => round($latLng['lng'], 6),
                ];
            }

            return [
                'success' => true,
                'coordinates' => $coordinates,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error during trilateration calculation: ' . $e->getMessage(),
            ];
        }
    }

    private function trilaterationSolver($P1, $P2, $P3, $r1, $r2, $r3)
    {

        $ex = $this->subtractVectors($P2, $P1);
        $d = $this->vectorLength($ex);


        if ($d < self::EPSILON) {
            $d = self::EPSILON;
        }

        $ex = $this->scaleVector($ex, 1/$d);

        $aux = $this->subtractVectors($P3, $P1);
        $i = $this->dotProduct($ex, $aux);
        $aux2 = $this->subtractVectors($aux, $this->scaleVector($ex, $i));
        $ey = $this->normalizeVector($aux2);

        if ($ey === null) {
            return null;
        }

        $ez = $this->crossProduct($ex, $ey);
        $j = $this->dotProduct($ey, $aux);


        $x = ($r1*$r1 - $r2*$r2 + $d*$d)/(2*$d);


        $y = ($r1*$r1 - $r3*$r3 + $i*$i + $j*$j)/(2*$j) - ($i/$j)*$x;


        $z2 = $r1*$r1 - $x*$x - $y*$y;


        if ($z2 < -self::DISTANCE_TOLERANCE) {
            return null;
        }

        $z = sqrt(abs($z2));


        $solution1 = $this->addVectors(
            $P1,
            $this->addVectors(
                $this->scaleVector($ex, $x),
                $this->addVectors(
                    $this->scaleVector($ey, $y),
                    $this->scaleVector($ez, $z)
                )
            )
        );


        $solution2 = $this->addVectors(
            $P1,
            $this->addVectors(
                $this->scaleVector($ex, $x),
                $this->addVectors(
                    $this->scaleVector($ey, $y),
                    $this->scaleVector($ez, -$z)
                )
            )
        );

        return [$solution1, $solution2];
    }

    private function latLngToCartesian($lat, $lng)
    {
        $earthRadius = 6371; // in kilometers

        $latRad = deg2rad($lat);
        $lngRad = deg2rad($lng);

        $x = $earthRadius * cos($latRad) * cos($lngRad);
        $y = $earthRadius * cos($latRad) * sin($lngRad);
        $z = $earthRadius * sin($latRad);

        return ['x' => $x, 'y' => $y, 'z' => $z];
    }

    private function cartesianToLatLng($cartesian)
    {
        $earthRadius = 6371; // in kilometers

        $x = $cartesian['x'];
        $y = $cartesian['y'];
        $z = $cartesian['z'];

        $latRad = asin($z / $earthRadius);
        $lngRad = atan2($y, $x);

        $lat = rad2deg($latRad);
        $lng = rad2deg($lngRad);

        return ['lat' => $lat, 'lng' => $lng];
    }

    private function subtractVectors($v1, $v2)
    {
        return [
            'x' => $v1['x'] - $v2['x'],
            'y' => $v1['y'] - $v2['y'],
            'z' => $v1['z'] - $v2['z'],
        ];
    }

    private function addVectors($v1, $v2)
    {
        return [
            'x' => $v1['x'] + $v2['x'],
            'y' => $v1['y'] + $v2['y'],
            'z' => $v1['z'] + $v2['z'],
        ];
    }

    private function scaleVector($v, $s)
    {
        return [
            'x' => $v['x'] * $s,
            'y' => $v['y'] * $s,
            'z' => $v['z'] * $s,
        ];
    }

    private function dotProduct($v1, $v2)
    {
        return $v1['x'] * $v2['x'] + $v1['y'] * $v2['y'] + $v1['z'] * $v2['z'];
    }

    private function crossProduct($v1, $v2)
    {
        return [
            'x' => $v1['y'] * $v2['z'] - $v1['z'] * $v2['y'],
            'y' => $v1['z'] * $v2['x'] - $v1['x'] * $v2['z'],
            'z' => $v1['x'] * $v2['y'] - $v1['y'] * $v2['x'],
        ];
    }

    private function vectorLength($v)
    {
        return sqrt($v['x'] ** 2 + $v['y'] ** 2 + $v['z'] ** 2);
    }

    private function normalizeVector($v)
    {
        $length = $this->vectorLength($v);
        if ($length == 0) {
            return null;
        }
        return [
            'x' => $v['x'] / $length,
            'y' => $v['y'] / $length,
            'z' => $v['z'] / $length,
        ];
    }
}
