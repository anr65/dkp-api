<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionDuration extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_id',
        'days',
        'price',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'sub_id');
    }

    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(SubToUser::class, 'sub_dur_id');
    }
}
