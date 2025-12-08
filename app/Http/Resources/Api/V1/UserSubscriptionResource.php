<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->subscription?->name,
            'duration' => $this->subscriptionDuration?->days,
            'valid_thru' => $this->valid_thru?->format('d.m.Y'),
        ];
    }
}
