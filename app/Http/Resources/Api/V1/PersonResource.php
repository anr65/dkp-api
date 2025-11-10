<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'surname' => $this->surname,
            'name' => $this->name,
            'fathername' => $this->fathername,
            'birthdate' => $this->birthdate?->format('d.m.Y'),
            'country' => $this->country,
            'index' => $this->index,
            'region' => $this->region,
            'passport' => new PassportResource($this->whenLoaded('passport')),
            'avatar' => $this->avatar,
        ];
    }
}
