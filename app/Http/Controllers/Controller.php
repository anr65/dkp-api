<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="ДКП API Documentation",
 *     version="1.0.0",
 *     description="API документация для системы договоров купли-продажи автомобилей",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
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
 */
abstract class Controller
{
    //
}
