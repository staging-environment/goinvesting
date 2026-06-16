<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'daily_spend_limit',
    'weekly_spend_limit',
    'alpaca_key_id',
    'alpaca_secret_key',
    'alpaca_account_id',
    'alpaca_is_paper',
    'bot_buy_threshold',
    'bot_take_profit',
    'bot_stop_loss',
    'bot_order_size',
    'bot_max_investment'
])]
#[Hidden(['password', 'remember_token', 'alpaca_secret_key'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'alpaca_key_id' => 'encrypted',
            'alpaca_secret_key' => 'encrypted',
            'alpaca_is_paper' => 'boolean',
            'daily_spend_limit' => 'float',
            'weekly_spend_limit' => 'float',
            'bot_buy_threshold' => 'float',
            'bot_take_profit' => 'float',
            'bot_stop_loss' => 'float',
            'bot_order_size' => 'float',
            'bot_max_investment' => 'float',
        ];
    }


    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }

    public function botExecutions()
    {
        return $this->hasMany(BotExecution::class);
    }

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

    public function getDailySpent(): float
    {
        return (float) $this->trades()
            ->whereDate('created_at', \Carbon\Carbon::today())
            ->where('is_dry_run', false)
            ->where('side', 'buy')
            ->sum(\DB::raw('qty * price'));
    }

    public function getWeeklySpent(): float
    {
        return (float) $this->trades()
            ->whereBetween('created_at', [
                \Carbon\Carbon::now()->startOfWeek(),
                \Carbon\Carbon::now()->endOfWeek()
            ])
            ->where('is_dry_run', false)
            ->where('side', 'buy')
            ->sum(\DB::raw('qty * price'));
    }

    public function hasExceededDailyLimit(float $amountToAdd = 0.0): bool
    {
        if (is_null($this->daily_spend_limit)) return false;
        return ($this->getDailySpent() + $amountToAdd) > (float)$this->daily_spend_limit;
    }

    public function hasExceededWeeklyLimit(float $amountToAdd = 0.0): bool
    {
        if (is_null($this->weekly_spend_limit)) return false;
        return ($this->getWeeklySpent() + $amountToAdd) > (float)$this->weekly_spend_limit;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
