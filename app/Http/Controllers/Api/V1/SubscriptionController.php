<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SubscriptionResource;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/subs/available",
     *     summary="Получить доступные подписки",
     *     description="Возвращает список доступных для покупки подписок со сроками действия и ценами",
     *     tags={"Subscriptions"},
     *     @OA\Response(
     *         response=200,
     *         description="Список доступных подписок",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Available subscriptions"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Договоры без ограничений"),
     *                     @OA\Property(property="durations", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="days", type="integer", example=30),
     *                             @OA\Property(property="price", type="number", example=299)
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function available(): JsonResponse
    {
        $subscriptions = Subscription::where('status', 'active')
            ->with(['durations' => function ($query) {
                $query->where('status', 'active');
            }])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Available subscriptions',
            'data' => SubscriptionResource::collection($subscriptions),
        ]);
    }
}
