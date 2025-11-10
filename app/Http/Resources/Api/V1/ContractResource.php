<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
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
            'status' => $this->status,
            'date' => $this->date?->format('d.m.Y'),
            'city' => $this->city,
            'seller' => new PersonResource($this->whenLoaded('seller')),
            'buyer' => new PersonResource($this->whenLoaded('buyer')),
            'car' => new CarResource($this->whenLoaded('car')),
            'price' => (string) $this->price,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
