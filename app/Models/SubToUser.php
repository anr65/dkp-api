<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubToUser extends Model
{
    use HasFactory;

    protected $table = 'sub_to_user';

    protected $fillable = [
        'user_id',
        'sub_dur_id',
        'sub_id',
        'valid_thru',
    ];

    protected $casts = [
        'valid_thru' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptionDuration(): BelongsTo
    {
        return $this->belongsTo(SubscriptionDuration::class, 'sub_dur_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'sub_id');
    }
}
