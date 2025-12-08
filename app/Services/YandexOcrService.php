<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexOcrService
{
    private string $baseUrl = 'https://ocr.api.cloud.yandex.net/ocr/v1/recognizeText';
    private string $apiKey;
    private string $folderId;

    public function __construct()
    {
        $this->apiKey = config('services.yandex_ocr.api_key');
        $this->folderId = config('services.yandex_ocr.folder_id');
    }

    public function recognizePassport(string $imageContent, string $mimeType): ?array
    {
        $response = $this->recognize($imageContent, $mimeType, 'passport');

        if (!$response) {
            return null;
        }

        return $this->parsePassportResponse($response);
    }

    public function recognizeSts(string $imageContent, string $mimeType): ?array
    {
        $responseFront = $this->recognize($imageContent, $mimeType, 'vehicle-registration-front');

        if (!$responseFront) {
            return null;
        }

        return $this->parseStsResponse($responseFront);
    }

    private function recognize(string $imageContent, string $mimeType, string $model): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'x-folder-id' => $this->folderId,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl, [
                'mimeType' => $mimeType,
                'languageCodes' => ['ru', 'en'],
                'model' => $model,
                'content' => base64_encode($imageContent),
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Yandex OCR API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Yandex OCR request failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function parsePassportResponse(array $response): array
    {
        $entities = $this->extractEntities($response);

        return [
            'surname' => $this->capitalizeProperName($entities['surname'] ?? null),
            'name' => $this->capitalizeProperName($entities['name'] ?? null),
            'fathername' => $this->capitalizeProperName($entities['middle_name'] ?? null),
            'birthdate' => $entities['birth_date'] ?? null,
            'passport' => [
                'serie' => $this->extractSerie($entities['number'] ?? ''),
                'number' => $this->extractNumber($entities['number'] ?? ''),
                'issuer' => $this->capitalizeText($entities['issued_by'] ?? null),
                'issue_date' => $entities['issue_date'] ?? null,
            ],
        ];
    }

    private function parseStsResponse(array $response): array
    {
        $entities = $this->extractEntities($response);

        return [
            'vin' => strtoupper($entities['stsfront_vin_number'] ?? ''),
            'sts' => $entities['stsfront_sts_number'] ?? null,
            'plates' => strtoupper($entities['stsfront_car_number'] ?? ''),
            'model' => $this->capitalizeText($this->buildCarModel($entities)),
            'type_category' => $this->capitalizeText($entities['stsfront_car_type'] ?? null),
            'issue_year' => $entities['stsfront_car_year'] ?? null,
            'engine_model' => $entities['stsfront_engine_model'] ?? null,
            'engine_number' => $entities['stsfront_engine_number'] ?? null,
            'chassis_number' => $entities['stsfront_car_chassis_number'] ?? null,
            'body_number' => $entities['stsfront_car_trailer_number'] ?? null,
            'color' => $this->capitalizeText($entities['stsfront_car_color'] ?? null),
        ];
    }

    private function extractEntities(array $response): array
    {
        $entities = [];

        if (isset($response['result']['textAnnotation']['entities'])) {
            foreach ($response['result']['textAnnotation']['entities'] as $entity) {
                $entities[$entity['name']] = $entity['text'];
            }
        }

        return $entities;
    }

    private function capitalizeProperName(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return null;
        }

        return mb_convert_case(mb_strtolower($text), MB_CASE_TITLE, 'UTF-8');
    }

    private function capitalizeText(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return null;
        }

        return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
    }

    private function extractSerie(string $passportNumber): string
    {
        $cleaned = preg_replace('/\s+/', '', $passportNumber);
        return mb_substr($cleaned, 0, 4);
    }

    private function extractNumber(string $passportNumber): string
    {
        $cleaned = preg_replace('/\s+/', '', $passportNumber);
        return mb_substr($cleaned, 4);
    }

    private function buildCarModel(array $entities): ?string
    {
        $brand = $entities['stsfront_car_brand'] ?? '';
        $model = $entities['stsfront_car_model'] ?? '';

        $fullModel = trim($brand . ' ' . $model);

        return $fullModel !== '' ? $fullModel : null;
    }
}
