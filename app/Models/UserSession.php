<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'last_seen_at',
        'device_info',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'device_info' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function updateLastSeen(): void
    {
        $this->update(['last_seen_at' => now()]);
    }
}
