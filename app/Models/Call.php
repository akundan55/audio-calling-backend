<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Call extends Model
{
    protected $fillable = [
        'caller_id',
        'receiver_id',
        'status',
        'started_at',
        'ended_at',
    ];

    /**
     * Caller (user who initiated the call)
     */
    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    /**
     * Receiver (user who receives the call)
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
