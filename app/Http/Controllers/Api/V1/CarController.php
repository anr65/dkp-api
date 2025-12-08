<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CarResource;
use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer|exists:cars,id',
            'vin' => 'nullable|string|max:50',
            'sts' => 'nullable|string|max:20',
            'pts' => 'nullable|string|max:20',
            'plates' => 'nullable|string|max:20',
            'model' => 'nullable|string|max:255',
            'type_category' => 'nullable|string|max:255',
            'issue_year' => 'nullable|string|max:4',
            'engine_model' => 'nullable|string|max:100',
            'engine_number' => 'nullable|string|max:100',
            'chassis_number' => 'nullable|string|max:100',
            'body_number' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:100',
        ]);

        $carData = [
            'vin' => $validated['vin'] ?? null,
            'sts' => $validated['sts'] ?? null,
            'pts' => $validated['pts'] ?? null,
            'plates' => $validated['plates'] ?? null,
            'model' => $validated['model'] ?? null,
            'type_category' => $validated['type_category'] ?? null,
            'issue_year' => $validated['issue_year'] ?? null,
            'engine_model' => $validated['engine_model'] ?? null,
            'engine_number' => $validated['engine_number'] ?? null,
            'chassis_number' => $validated['chassis_number'] ?? null,
            'body_number' => $validated['body_number'] ?? null,
            'color' => $validated['color'] ?? null,
        ];

        $isUpdate = !empty($validated['id']);

        if ($isUpdate) {
            $car = Car::findOrFail($validated['id']);
            $car->update($carData);
        } else {
            $car = Car::create($carData);
        }

        return response()->json([
            'success' => true,
            'message' => $isUpdate ? 'Car updated successfully' : 'Car created successfully',
            'data' => new CarResource($car),
        ], $isUpdate ? 200 : 201);
    }
}
