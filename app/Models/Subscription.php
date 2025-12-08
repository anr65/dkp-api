<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

    public function durations(): HasMany
    {
        return $this->hasMany(SubscriptionDuration::class, 'sub_id');
    }

    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(SubToUser::class, 'sub_id');
    }
}
