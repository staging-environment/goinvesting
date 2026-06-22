<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    protected $fillable = [
        'user_id',
        'bot_execution_id',
        'broker_order_id',
        'symbol',
        'qty',
        'price',
        'side',
        'status',
        'is_dry_run',
        'pnl'
    ];

    protected $casts = [
        'qty' => 'float',
        'price' => 'float',
        'is_dry_run' => 'boolean',
        'pnl' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function botExecution(): BelongsTo
    {
        return $this->belongsTo(BotExecution::class);
    }
}
