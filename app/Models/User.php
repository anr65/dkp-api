<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Platform\Models\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'telegram_id',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
        'email_verified_at' => 'datetime',
    ];

    protected $allowedFilters = [
        'id' => Where::class,
        'name' => Like::class,
        'email' => Like::class,
        'telegram_id' => Like::class,
        'updated_at' => WhereDateStartEnd::class,
        'created_at' => WhereDateStartEnd::class,
    ];

    protected $allowedSorts = [
        'id',
        'name',
        'email',
        'telegram_id',
        'updated_at',
        'created_at',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SubToUser::class);
    }

    public function policies(): BelongsToMany
    {
        return $this->belongsToMany(Policy::class, 'user_policies')
            ->withPivot('signed_at')
            ->withTimestamps();
    }

    public function getFullAttribute(): string
    {
        return $this->name ?: ('TG: ' . $this->telegram_id);
    }
}
