<?php

namespace App\OpenApi;

/**
 * @OA\Schema(
 *     schema="Contract",
 *     title="Contract",
 *     description="Договор купли-продажи автомобиля",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="status", type="string", enum={"generated", "draft"}, example="draft"),
 *     @OA\Property(property="date", type="string", example="10.11.2025"),
 *     @OA\Property(property="city", type="string", example="Москва"),
 *     @OA\Property(property="seller", ref="#/components/schemas/Person"),
 *     @OA\Property(property="buyer", ref="#/components/schemas/Person"),
 *     @OA\Property(property="car", ref="#/components/schemas/Car"),
 *     @OA\Property(property="price", type="string", example="1500000.00"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Person",
 *     title="Person",
 *     description="Физическое лицо (продавец или покупатель)",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="surname", type="string", example="Иванов"),
 *     @OA\Property(property="name", type="string", example="Иван"),
 *     @OA\Property(property="fathername", type="string", example="Иванович", nullable=true),
 *     @OA\Property(property="birthdate", type="string", example="01.01.1990"),
 *     @OA\Property(property="country", type="string", example="Россия"),
 *     @OA\Property(property="index", type="string", example="123456"),
 *     @OA\Property(property="region", type="string", example="Москва"),
 *     @OA\Property(property="passport", ref="#/components/schemas/Passport"),
 *     @OA\Property(property="avatar", type="string", nullable=true, example="https://example.com/avatar.jpg")
 * )
 *
 * @OA\Schema(
 *     schema="Passport",
 *     title="Passport",
 *     description="Паспортные данные",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="serie", type="string", example="1234"),
 *     @OA\Property(property="number", type="string", example="567890"),
 *     @OA\Property(property="issuer", type="string", example="ОУФМС России по г. Москве"),
 *     @OA\Property(property="issue_date", type="string", example="01.01.2010")
 * )
 *
 * @OA\Schema(
 *     schema="Car",
 *     title="Car",
 *     description="Автомобиль",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="vin", type="string", example="WVWZZZ1JZXW123456"),
 *     @OA\Property(property="sts", type="string", example="77AA123456"),
 *     @OA\Property(property="pts", type="string", example="77ББ123456"),
 *     @OA\Property(property="plates", type="string", example="А123БВ777"),
 *     @OA\Property(property="model", type="string", example="Toyota Camry"),
 *     @OA\Property(property="type_category", type="string", example="Легковой автомобиль"),
 *     @OA\Property(property="issue_year", type="string", example="2020"),
 *     @OA\Property(property="engine_model", type="string", example="2AR-FE"),
 *     @OA\Property(property="engine_number", type="string", example="1234567"),
 *     @OA\Property(property="chassis_number", type="string", example="12345678901234567", nullable=true),
 *     @OA\Property(property="body_number", type="string", example="12345678901234567"),
 *     @OA\Property(property="color", type="string", example="Черный")
 * )
 */
class Schemas
{
    // This class is only used for storing OpenAPI schema annotations
}