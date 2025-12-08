<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserSubscriptionResource;
use App\Models\SubToUser;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(
        private TelegramService $telegramService
    ) {}

    /**
     * Login via Telegram
     *
     * @OA\Post(
     *     path="/login",
     *     summary="Авторизация через Telegram",
     *     description="Двухэтапная авторизация: 1) Отправка кода верификации, 2) Проверка кода и создание сессии",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phone_number", type="string", example="+79991234567", description="Номер телефона"),
     *             @OA\Property(property="request_id", type="string", example="req_123abc", description="ID запроса (получается на первом шаге)"),
     *             @OA\Property(property="code", type="string", example="12345", description="Код верификации из Telegram")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешная авторизация или отправка кода",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="success", type="boolean", example=true),
     *                     @OA\Property(property="request_id", type="string", example="req_123abc"),
     *                     @OA\Property(property="message", type="string", example="Verification code sent")
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="success", type="boolean", example=true),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="avatar", type="string", nullable=true, example="https://example.com/avatar.jpg"),
     *                         @OA\Property(property="created_at", type="string", format="date-time")
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неверный код верификации",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid verification code")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'request_id' => 'required_with:code|string',
            'code' => 'nullable|string|min:4|max:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $phoneNumber = $request->input('phone_number');
        $requestId = $request->input('request_id');
        $code = $request->input('code');

        // If no request_id provided, send verification message
        if (!$requestId) {
            $result = $this->telegramService->sendVerificationMessage($phoneNumber);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send verification code'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'request_id' => $result['request_id'],
                'message' => 'Verification code sent'
            ]);
        }

        // Verify the code
        $verificationResult = $this->telegramService->checkVerificationStatus($requestId, $code);

        if (!$verificationResult || ($verificationResult['status'] ?? '') !== 'code_valid') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code'
            ], 401);
        }

        // Get user info from verification result
        $telegramId = $verificationResult['user']['id'] ?? null;
        $firstName = $verificationResult['user']['first_name'] ?? 'User';
        $lastName = $verificationResult['user']['last_name'] ?? '';
        $photoUrl = $verificationResult['user']['photo_url'] ?? null;

        if (!$telegramId) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get Telegram user data'
            ], 500);
        }

        // Find or create user
        $user = User::firstOrCreate(
            ['telegram_id' => (string)$telegramId],
            [
                'name' => trim($firstName . ' ' . $lastName),
                'avatar' => $photoUrl,
            ]
        );

        // Login user (create session)
        Auth::login($user);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'created_at' => $user->created_at,
            ]
        ]);
    }

    /**
     * Logout user
     *
     * @OA\Post(
     *     path="/logout",
     *     summary="Выход из системы",
     *     description="Завершение сессии пользователя",
     *     tags={"Authentication"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Успешный выход",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        Auth::logout();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get current user info
     *
     * @OA\Get(
     *     path="/current",
     *     summary="Получить данные текущего пользователя",
     *     description="Возвращает информацию о текущем авторизованном пользователе",
     *     tags={"Authentication"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Данные пользователя",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="avatar", type="string", nullable=true, example="https://example.com/avatar.jpg"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="sub", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer", example=778),
     *                     @OA\Property(property="name", type="string", example="Договоры без ограничений"),
     *                     @OA\Property(property="duration", type="integer", example=365),
     *                     @OA\Property(property="valid_thru", type="string", example="15.09.2026")
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
     *
     * @return JsonResponse
     */
    public function current(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $activeSubscription = SubToUser::where('user_id', $user->id)
            ->where('valid_thru', '>', now())
            ->with(['subscription', 'subscriptionDuration'])
            ->first();

        $subData = $activeSubscription
            ? new UserSubscriptionResource($activeSubscription)
            : null;

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'created_at' => $user->created_at,
                'sub' => $subData,
            ]
        ]);
    }
}
