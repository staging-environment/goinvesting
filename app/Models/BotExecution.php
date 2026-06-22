<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BotExecution extends Model
{
    protected $table = 'bot_executions';

    protected $fillable = [
        'user_id',
        'started_at',
        'finished_at',
        'status',
        'is_dry_run',
        'is_paper',
        'output'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'is_dry_run' => 'boolean',
        'is_paper' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }
}
