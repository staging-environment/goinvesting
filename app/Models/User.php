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
    'wizard_completed',
    'daily_spend_limit',
    'weekly_spend_limit',
    'monthly_spend_limit',
    'alpaca_key_id',
    'alpaca_secret_key',
    'alpaca_account_id',
    'alpaca_live_key_id',
    'alpaca_live_secret_key',
    'alpaca_live_account_id',
    'alpaca_is_paper',
    'alpaca_live_consent',
    'bot_buy_threshold',
    'bot_take_profit',
    'bot_stop_loss',
    'bot_order_size',
    'bot_max_investment',
    'live_bot_buy_threshold',
    'live_bot_take_profit',
    'live_bot_stop_loss',
    'live_bot_order_size',
    'live_bot_max_investment',
    'live_daily_spend_limit',
    'live_weekly_spend_limit',
    'live_monthly_spend_limit'
])]
#[Hidden(['password', 'remember_token', 'alpaca_secret_key', 'alpaca_live_secret_key'])]
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
            'alpaca_live_key_id' => 'encrypted',
            'alpaca_live_secret_key' => 'encrypted',
            'alpaca_is_paper' => 'boolean',
            'alpaca_live_consent' => 'boolean',
            'wizard_completed' => 'boolean',
            'daily_spend_limit' => 'float',
            'weekly_spend_limit' => 'float',
            'monthly_spend_limit' => 'float',
            'bot_buy_threshold' => 'float',
            'bot_take_profit' => 'float',
            'bot_stop_loss' => 'float',
            'bot_order_size' => 'float',
            'bot_max_investment' => 'float',
            'live_bot_buy_threshold' => 'float',
            'live_bot_take_profit' => 'float',
            'live_bot_stop_loss' => 'float',
            'live_bot_order_size' => 'float',
            'live_bot_max_investment' => 'float',
            'live_daily_spend_limit' => 'float',
            'live_weekly_spend_limit' => 'float',
            'live_monthly_spend_limit' => 'float',
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

    public function getDailySpent(bool $isPaper = null): float
    {
        $isPaper = $isPaper ?? (bool)$this->alpaca_is_paper;
        return (float) $this->trades()
            ->whereDate('created_at', \Carbon\Carbon::today())
            ->where('is_dry_run', $isPaper)
            ->where('side', 'buy')
            ->whereNotIn('status', ['canceled', 'rejected', 'expired'])
            ->sum(\DB::raw('qty * price'));
    }

    public function getWeeklySpent(bool $isPaper = null): float
    {
        $isPaper = $isPaper ?? (bool)$this->alpaca_is_paper;
        return (float) $this->trades()
            ->whereBetween('created_at', [
                \Carbon\Carbon::now()->startOfWeek(),
                \Carbon\Carbon::now()->endOfWeek()
            ])
            ->where('is_dry_run', $isPaper)
            ->where('side', 'buy')
            ->whereNotIn('status', ['canceled', 'rejected', 'expired'])
            ->sum(\DB::raw('qty * price'));
    }

    public function getMonthlySpent(bool $isPaper = null): float
    {
        $isPaper = $isPaper ?? (bool)$this->alpaca_is_paper;
        return (float) $this->trades()
            ->whereBetween('created_at', [
                \Carbon\Carbon::now()->startOfMonth(),
                \Carbon\Carbon::now()->endOfMonth()
            ])
            ->where('is_dry_run', $isPaper)
            ->where('side', 'buy')
            ->whereNotIn('status', ['canceled', 'rejected', 'expired'])
            ->sum(\DB::raw('qty * price'));
    }

    public function hasExceededDailyLimit(float $amountToAdd = 0.0, bool $isPaper = null): bool
    {
        $isPaper = $isPaper ?? (bool)$this->alpaca_is_paper;
        $limit = $isPaper ? $this->daily_spend_limit : $this->live_daily_spend_limit;
        if (is_null($limit)) return false;
        return ($this->getDailySpent($isPaper) + $amountToAdd) > (float)$limit;
    }

    public function hasExceededWeeklyLimit(float $amountToAdd = 0.0, bool $isPaper = null): bool
    {
        $isPaper = $isPaper ?? (bool)$this->alpaca_is_paper;
        $limit = $isPaper ? $this->weekly_spend_limit : $this->live_weekly_spend_limit;
        if (is_null($limit)) return false;
        return ($this->getWeeklySpent($isPaper) + $amountToAdd) > (float)$limit;
    }

    public function hasExceededMonthlyLimit(float $amountToAdd = 0.0, bool $isPaper = null): bool
    {
        $isPaper = $isPaper ?? (bool)$this->alpaca_is_paper;
        $limit = $isPaper ? $this->monthly_spend_limit : $this->live_monthly_spend_limit;
        if (is_null($limit)) return false;
        return ($this->getMonthlySpent($isPaper) + $amountToAdd) > (float)$limit;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
