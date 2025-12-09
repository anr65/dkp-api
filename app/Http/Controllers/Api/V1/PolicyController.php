<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PolicyResource;
use App\Models\Policy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PolicyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/policies/main",
     *     summary="Получить список политик конфиденциальности",
     *     tags={"Политики"},
     *     @OA\Response(
     *         response=200,
     *         description="Список политик",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Privacy policies"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Политика конфиденциальности"),
     *                     @OA\Property(property="url", type="string", example="https://example.com/privacy")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function main(): JsonResponse
    {
        $policies = Policy::active()->get();

        return response()->json([
            'success' => true,
            'message' => 'Privacy policies',
            'data' => PolicyResource::collection($policies),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/v1/policies/user",
     *     summary="Получить статус подписания политик пользователем",
     *     description="Возвращает список политик с информацией о том, подписал ли их текущий пользователь",
     *     tags={"Политики"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список политик со статусом подписания",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Privacy policies of user"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="signed", type="boolean", example=true),
     *                     @OA\Property(property="url", type="string", example="https://example.com/privacy")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function user(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $policies = Policy::active()->get();
        $signedPolicyIds = $user->policies()
            ->whereNotNull('user_policies.signed_at')
            ->pluck('policies.id')
            ->toArray();

        $data = $policies->map(function ($policy) use ($signedPolicyIds) {
            return [
                'id' => $policy->id,
                'signed' => in_array($policy->id, $signedPolicyIds),
                'url' => $policy->url,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Privacy policies of user',
            'data' => $data,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/v1/policies/sign",
     *     summary="Подписать политики",
     *     description="Подписывает указанные политики для текущего пользователя",
     *     tags={"Политики"},
     *     security={{"cookieAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"policies"},
     *             @OA\Property(property="policies", type="array", description="Массив ID политик для подписания",
     *                 @OA\Items(type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Политики успешно подписаны",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Policies signed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
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
    public function sign(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $validated = $request->validate([
            'policies' => 'required|array',
            'policies.*' => 'integer|exists:policies,id',
        ]);

        $policyIds = $validated['policies'];
        $now = now();

        foreach ($policyIds as $policyId) {
            $user->policies()->syncWithoutDetaching([
                $policyId => ['signed_at' => $now]
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Policies signed successfully',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/v1/policies/unsign",
     *     summary="Отозвать подписание политик",
     *     description="Отзывает подписание указанных политик для текущего пользователя",
     *     tags={"Политики"},
     *     security={{"cookieAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"policies"},
     *             @OA\Property(property="policies", type="array", description="Массив ID политик для отзыва",
     *                 @OA\Items(type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Подписание политик успешно отозвано",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Policies unsigned successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
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
    public function unsign(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $validated = $request->validate([
            'policies' => 'required|array',
            'policies.*' => 'integer|exists:policies,id',
        ]);

        $policyIds = $validated['policies'];

        foreach ($policyIds as $policyId) {
            $user->policies()->updateExistingPivot($policyId, ['signed_at' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Policies unsigned successfully',
        ]);
    }
}
