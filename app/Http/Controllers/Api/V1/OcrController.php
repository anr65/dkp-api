<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CarResource;
use App\Http\Resources\Api\V1\PersonResource;
use App\Models\Car;
use App\Models\Passport;
use App\Models\Person;
use App\Services\YandexOcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OcrController extends Controller
{
    public function __construct(
        private YandexOcrService $ocrService
    ) {}

    public function passport(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,pdf|max:20480',
        ]);

        $file = $request->file('file');
        $imageContent = file_get_contents($file->getRealPath());
        $mimeType = $file->getMimeType();

        $ocrData = $this->ocrService->recognizePassport($imageContent, $mimeType);

        if (!$ocrData) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to recognize passport',
            ], 422);
        }

        $passport = Passport::create([
            'serie' => $ocrData['passport']['serie'],
            'number' => $ocrData['passport']['number'],
            'issuer' => $ocrData['passport']['issuer'],
            'issue_date' => $this->parseDate($ocrData['passport']['issue_date']),
        ]);

        $person = Person::create([
            'surname' => $ocrData['surname'],
            'name' => $ocrData['name'],
            'fathername' => $ocrData['fathername'],
            'birthdate' => $this->parseDate($ocrData['birthdate']),
            'passport_id' => $passport->id,
        ]);

        $person->load('passport');

        return response()->json([
            'success' => true,
            'message' => 'Passport recognized successfully',
            'data' => new PersonResource($person),
        ]);
    }

    public function sts(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,pdf|max:20480',
        ]);

        $file = $request->file('file');
        $imageContent = file_get_contents($file->getRealPath());
        $mimeType = $file->getMimeType();

        $ocrData = $this->ocrService->recognizeSts($imageContent, $mimeType);

        if (!$ocrData) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to recognize STS',
            ], 422);
        }

        $car = Car::create([
            'vin' => $ocrData['vin'],
            'sts' => $ocrData['sts'],
            'plates' => $ocrData['plates'],
            'model' => $ocrData['model'],
            'type_category' => $ocrData['type_category'],
            'issue_year' => $ocrData['issue_year'],
            'engine_model' => $ocrData['engine_model'],
            'engine_number' => $ocrData['engine_number'],
            'chassis_number' => $ocrData['chassis_number'],
            'body_number' => $ocrData['body_number'],
            'color' => $ocrData['color'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'STS recognized successfully',
            'data' => new CarResource($car),
        ]);
    }

    private function parseDate(?string $dateString): ?string
    {
        if (!$dateString) {
            return null;
        }

        $formats = ['d.m.Y', 'd/m/Y', 'Y-m-d'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }
}
