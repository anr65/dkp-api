<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CarResource;
use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarController extends Controller
{
    /**
     * @OA\Post(
     *     path="/v1/car",
     *     summary="Создать или обновить автомобиль",
     *     tags={"Автомобили"},
     *     security={{"cookieAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", nullable=true, description="ID автомобиля для обновления", example=1),
     *             @OA\Property(property="vin", type="string", nullable=true, example="XTA210990Y2696969", maxLength=50),
     *             @OA\Property(property="sts", type="string", nullable=true, example="77УУ123456", maxLength=20),
     *             @OA\Property(property="pts", type="string", nullable=true, example="77УА123456", maxLength=20),
     *             @OA\Property(property="plates", type="string", nullable=true, example="А123БВ777", maxLength=20),
     *             @OA\Property(property="model", type="string", nullable=true, example="LADA VESTA", maxLength=255),
     *             @OA\Property(property="type_category", type="string", nullable=true, example="B", maxLength=255),
     *             @OA\Property(property="issue_year", type="string", nullable=true, example="2020", maxLength=4),
     *             @OA\Property(property="engine_model", type="string", nullable=true, example="21129", maxLength=100),
     *             @OA\Property(property="engine_number", type="string", nullable=true, example="1234567", maxLength=100),
     *             @OA\Property(property="chassis_number", type="string", nullable=true, example="отсутствует", maxLength=100),
     *             @OA\Property(property="body_number", type="string", nullable=true, example="XTA210990Y2696969", maxLength=100),
     *             @OA\Property(property="color", type="string", nullable=true, example="белый", maxLength=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Автомобиль успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Автомобиль успешно создан"),
     *             @OA\Property(property="data", ref="#/components/schemas/Car")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Автомобиль успешно обновлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Автомобиль успешно обновлён"),
     *             @OA\Property(property="data", ref="#/components/schemas/Car")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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
