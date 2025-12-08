<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PersonResource;
use App\Models\Passport;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer|exists:people,id',
            'surname' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'fathername' => 'nullable|string|max:255',
            'birthdate' => 'nullable|string',
            'country' => 'nullable|string|max:255',
            'index' => 'nullable|string|max:20',
            'region' => 'nullable|string|max:255',
            'avatar' => 'nullable|string|max:500',
            'passport' => 'required|array',
            'passport.id' => 'nullable|integer|exists:passports,id',
            'passport.serie' => 'required|string|max:10',
            'passport.number' => 'required|string|max:20',
            'passport.issuer' => 'nullable|string|max:500',
            'passport.issue_date' => 'nullable|string',
        ]);

        $person = DB::transaction(function () use ($validated) {
            $passportData = [
                'serie' => $validated['passport']['serie'],
                'number' => $validated['passport']['number'],
                'issuer' => $validated['passport']['issuer'] ?? null,
                'issue_date' => $this->parseDate($validated['passport']['issue_date'] ?? null),
            ];

            if (!empty($validated['passport']['id'])) {
                $passport = Passport::findOrFail($validated['passport']['id']);
                $passport->update($passportData);
            } else {
                $passport = Passport::create($passportData);
            }

            $personData = [
                'surname' => $validated['surname'],
                'name' => $validated['name'],
                'fathername' => $validated['fathername'] ?? null,
                'birthdate' => $this->parseDate($validated['birthdate'] ?? null),
                'country' => $validated['country'] ?? null,
                'index' => $validated['index'] ?? null,
                'region' => $validated['region'] ?? null,
                'avatar' => $validated['avatar'] ?? null,
                'passport_id' => $passport->id,
            ];

            if (!empty($validated['id'])) {
                $person = Person::findOrFail($validated['id']);
                $person->update($personData);
            } else {
                $person = Person::create($personData);
            }

            return $person;
        });

        $person->load('passport');

        $isUpdate = !empty($validated['id']);

        return response()->json([
            'success' => true,
            'message' => $isUpdate ? 'Person updated successfully' : 'Person created successfully',
            'data' => new PersonResource($person),
        ], $isUpdate ? 200 : 201);
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
