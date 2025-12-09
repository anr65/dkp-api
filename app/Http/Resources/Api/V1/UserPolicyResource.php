<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPolicyResource extends JsonResource
{
    protected bool $signed;

    public function __construct($resource, bool $signed = false)
    {
        parent::__construct($resource);
        $this->signed = $signed;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'signed' => $this->signed,
            'url' => $this->url,
        ];
    }
}
