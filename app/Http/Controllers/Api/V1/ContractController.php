<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreContractRequest;
use App\Http\Requests\Api\V1\UpdateContractRequest;
use App\Http\Resources\Api\V1\ContractResource;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/v1/contracts",
     *     summary="Получить список договоров",
     *     description="Возвращает список всех договоров с пагинацией",
     *     tags={"Contracts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(
     *         name="pageNo",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *         @OA\Schema(type="integer", default=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="pageSize",
     *         in="query",
     *         description="Количество элементов на странице",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список договоров",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contracts list"),
     *             @OA\Property(property="items", type="array",
     *                 @OA\Items(ref="#/components/schemas/Contract")
     *             ),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="pageNo", type="integer", example=1),
     *                 @OA\Property(property="pageSize", type="integer", example=10),
     *                 @OA\Property(property="totalPages", type="integer", example=10)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $pageNo = (int) $request->input('pageNo', 1);
        $pageSize = (int) $request->input('pageSize', 10);

        $contracts = Contract::with(['seller.passport', 'buyer.passport', 'car'])
            ->paginate($pageSize, ['*'], 'page', $pageNo);

        return response()->json([
            'success' => true,
            'message' => 'Contracts list',
            'items' => ContractResource::collection($contracts->items()),
            'pagination' => [
                'total' => $contracts->total(),
                'pageNo' => $contracts->currentPage(),
                'pageSize' => $contracts->perPage(),
                'totalPages' => $contracts->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/v1/contracts",
     *     summary="Создать новый договор",
     *     description="Создает новый договор купли-продажи автомобиля",
     *     tags={"Contracts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"date", "city", "seller_id", "buyer_id", "price", "car_id"},
     *             @OA\Property(property="status", type="string", enum={"generated", "draft"}, example="draft"),
     *             @OA\Property(property="date", type="string", format="date", example="2025-11-10"),
     *             @OA\Property(property="city", type="string", example="Москва", maxLength=255),
     *             @OA\Property(property="seller_id", type="integer", example=1),
     *             @OA\Property(property="buyer_id", type="integer", example=2),
     *             @OA\Property(property="price", type="number", format="float", example=1500000.00, minimum=0),
     *             @OA\Property(property="car_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Договор успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contract created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Contract")
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
    public function store(StoreContractRequest $request): JsonResponse
    {
        $contract = Contract::create($request->validated());
        $contract->load(['seller.passport', 'buyer.passport', 'car']);

        return response()->json([
            'success' => true,
            'message' => 'Contract created successfully',
            'data' => new ContractResource($contract),
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/v1/contracts/{id}",
     *     summary="Получить договор по ID",
     *     description="Возвращает подробную информацию о договоре",
     *     tags={"Contracts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID договора",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Детали договора",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contract details"),
     *             @OA\Property(property="data", ref="#/components/schemas/Contract")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Договор не найден"
     *     )
     * )
     */
    public function show(Contract $contract): JsonResponse
    {
        $contract->load(['seller.passport', 'buyer.passport', 'car']);

        return response()->json([
            'success' => true,
            'message' => 'Contract details',
            'data' => new ContractResource($contract),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/v1/contracts/{id}",
     *     summary="Обновить договор",
     *     description="Обновляет существующий договор купли-продажи",
     *     tags={"Contracts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID договора",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"generated", "draft"}, example="draft"),
     *             @OA\Property(property="date", type="string", format="date", example="2025-11-10"),
     *             @OA\Property(property="city", type="string", example="Москва", maxLength=255),
     *             @OA\Property(property="seller_id", type="integer", example=1),
     *             @OA\Property(property="buyer_id", type="integer", example=2),
     *             @OA\Property(property="price", type="number", format="float", example=1500000.00, minimum=0),
     *             @OA\Property(property="car_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Договор успешно обновлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contract updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Contract")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Договор не найден"
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
    public function update(UpdateContractRequest $request, Contract $contract): JsonResponse
    {
        $contract->update($request->validated());
        $contract->load(['seller.passport', 'buyer.passport', 'car']);

        return response()->json([
            'success' => true,
            'message' => 'Contract updated successfully',
            'data' => new ContractResource($contract),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/v1/contracts/{id}",
     *     summary="Удалить договор",
     *     description="Удаляет договор по ID",
     *     tags={"Contracts"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID договора",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Договор успешно удален",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Contract deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Договор не найден"
     *     )
     * )
     */
    public function destroy(Contract $contract): JsonResponse
    {
        $contract->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contract deleted successfully',
        ]);
    }
}
