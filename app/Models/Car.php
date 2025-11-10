<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'vin',
        'sts',
        'pts',
        'plates',
        'model',
        'type_category',
        'issue_year',
        'engine_model',
        'engine_number',
        'chassis_number',
        'body_number',
        'color',
    ];

    protected $casts = [
        'issue_year' => 'integer',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
