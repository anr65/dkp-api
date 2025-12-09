<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="ДКП API",
 *     version="1.0.0"
 * )
 *
 * @OA\Server(
 *     url="/api",
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="cookieAuth",
 *     type="apiKey",
 *     in="cookie",
 *     name="laravel_session"
 * )
 *
 * @OA\Tag(name="Авторизация")
 * @OA\Tag(name="Автомобили")
 * @OA\Tag(name="Договоры")
 * @OA\Tag(name="Распознавание")
 * @OA\Tag(name="Персоны")
 * @OA\Tag(name="Политики")
 * @OA\Tag(name="Подписки")
 */
abstract class Controller
{
    //
}
