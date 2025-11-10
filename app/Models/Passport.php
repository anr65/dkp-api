<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Passport extends Model
{
    use HasFactory;

    protected $fillable = [
        'serie',
        'number',
        'issuer',
        'issue_date',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    public function person(): HasOne
    {
        return $this->hasOne(Person::class);
    }
}
