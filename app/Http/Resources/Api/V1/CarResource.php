<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
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
            'vin' => $this->vin,
            'sts' => $this->sts,
            'pts' => $this->pts,
            'plates' => $this->plates,
            'model' => $this->model,
            'type_category' => $this->type_category,
            'issue_year' => (string) $this->issue_year,
            'engine_model' => $this->engine_model,
            'engine_number' => $this->engine_number,
            'chassis_number' => $this->chassis_number,
            'body_number' => $this->body_number,
            'color' => $this->color,
        ];
    }
}
