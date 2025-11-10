<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'surname',
        'fathername',
        'birthdate',
        'country',
        'index',
        'region',
        'passport_id',
        'avatar',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function passport(): BelongsTo
    {
        return $this->belongsTo(Passport::class);
    }

    public function contractsAsSeller(): HasMany
    {
        return $this->hasMany(Contract::class, 'seller_id');
    }

    public function contractsAsBuyer(): HasMany
    {
        return $this->hasMany(Contract::class, 'buyer_id');
    }
}
