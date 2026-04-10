<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ClassifyController extends Controller
{
    public function classify(Request $request): JsonResponse
    {
        $name = $request->query('name');

        // 400 — missing or empty name
        if (is_null($name) || $name === '') {
            return response()->json(
                ['status' => 'error', 'message' => 'Missing or empty name parameter'],
                400
            );
        }

        // 422 — name must be a string (arrays come in as non-string)
        if (!is_string($name)) {
            return response()->json(
                ['status' => 'error', 'message' => 'name must be a string'],
                422
            );
        }

        // Call Genderize API
        try {
            $response = Http::timeout(5)->get('https://api.genderize.io', [
                'name' => $name,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json(
                ['status' => 'error', 'message' => 'Failed to reach upstream API'],
                502
            );
        }

        if (!$response->successful()) {
            return response()->json(
                ['status' => 'error', 'message' => 'Upstream API returned an error'],
                502
            );
        }

        $apiData = $response->json();

        // Edge case: null gender or zero count
        if (empty($apiData['gender']) || empty($apiData['count'])) {
            return response()->json(
                ['status' => 'error', 'message' => 'No prediction available for the provided name'],
                200
            );
        }

        $probability  = $apiData['probability'];
        $sample_size  = $apiData['count'];
        $is_confident = ($probability >= 0.7 && $sample_size >= 100);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'name'         => strtolower($apiData['name']),
                'gender'       => $apiData['gender'],
                'probability'  => $probability,
                'sample_size'  => $sample_size,
                'is_confident' => $is_confident,
                'processed_at' => now()->utc()->toIso8601String(),
            ],
        ], 200);
    }
}
