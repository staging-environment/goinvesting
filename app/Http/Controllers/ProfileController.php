<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Update the user's Alpaca credentials.
     */
    public function updateAlpaca(Request $request): RedirectResponse
    {
        $request->validate([
            'alpaca_key_id' => 'nullable|string|max:255',
            'alpaca_secret_key' => 'nullable|string|max:255',
            'alpaca_account_id' => 'nullable|string|max:255',
            'alpaca_live_key_id' => 'nullable|string|max:255',
            'alpaca_live_secret_key' => 'nullable|string|max:255',
            'alpaca_live_account_id' => 'nullable|string|max:255',
            'alpaca_is_paper' => 'nullable|boolean'
        ]);

        $user = $request->user();
        
        // Paper
        $keyId = $request->input('alpaca_key_id');
        $secretKey = $request->filled('alpaca_secret_key') ? $request->input('alpaca_secret_key') : $user->alpaca_secret_key;
        $accountId = $request->input('alpaca_account_id');
        
        // Live
        $liveKeyId = $request->input('alpaca_live_key_id');
        $liveSecretKey = $request->filled('alpaca_live_secret_key') ? $request->input('alpaca_live_secret_key') : $user->alpaca_live_secret_key;
        $liveAccountId = $request->input('alpaca_live_account_id');
        
        $isPaper = $request->has('alpaca_is_paper') ? (bool)$request->input('alpaca_is_paper') : false;

        $connectionSuccess = true;
        $connectionMessage = '';

        // Validate the ACTIVE credentials
        $activeKeyId = $isPaper ? $keyId : $liveKeyId;
        $activeSecretKey = $isPaper ? $secretKey : $liveSecretKey;
        $activeAccountId = $isPaper ? $accountId : $liveAccountId;
        $activeModeText = $isPaper ? 'Simulación (Paper)' : 'Real (Live)';

        if ($activeKeyId && $activeSecretKey) {
            try {
                $tempService = new \App\Services\AlpacaService($activeKeyId, $activeSecretKey, $activeAccountId, $isPaper);
                $accountInfo = $tempService->getAccountInfo();
                if (!$accountInfo) {
                    $connectionSuccess = false;
                    if (!$isPaper) {
                        $connectionMessage = "No se pudo conectar con Alpaca en modo Real (Live).<br><br>" .
                            "<strong class='text-slate-100'>¿Tu cuenta es nueva?</strong> Alpaca suele tardar entre 24 y 72 horas hábiles en verificar y activar nuevas cuentas reales. Hasta que tu cuenta no esté completamente aprobada y activa por su equipo, tus llaves de API reales devolverán un error de conexión no autorizada.<br><br>" .
                            "Puedes comprobar el estado de tu aprobación iniciando sesión en tu <a href='https://app.alpaca.markets' target='_blank' class='text-indigo-400 hover:text-indigo-300 underline font-extrabold transition'>Panel de Control de Alpaca</a>, contactar directamente con su soporte en <a href='mailto:support@alpaca.markets' class='text-indigo-400 hover:text-indigo-300 underline font-extrabold transition'>support@alpaca.markets</a> o visitar el <a href='https://support.alpaca.markets' target='_blank' class='text-indigo-400 hover:text-indigo-300 underline font-extrabold transition'>Centro de Ayuda de Alpaca</a>.";
                    } else {
                        $connectionMessage = 'No se pudo conectar con Alpaca. Por favor, verifica tus credenciales (asegúrate de que correspondan al modo de Simulación/Paper activo).';
                    }
                }
            } catch (\Exception $e) {
                $connectionSuccess = false;
                $connectionMessage = 'Error al verificar la conexión: ' . $e->getMessage();
            }
        } else {
            // Only require keys for the active mode
            $connectionSuccess = false;
            $connectionMessage = 'Las credenciales de Alpaca para el modo activo (' . $activeModeText . ') están incompletas.';
        }

        $updateData = [
            'alpaca_key_id' => $keyId,
            'alpaca_account_id' => $accountId,
            'alpaca_live_key_id' => $liveKeyId,
            'alpaca_live_account_id' => $liveAccountId,
            'alpaca_is_paper' => $isPaper
        ];
        if ($request->filled('alpaca_secret_key')) {
            $updateData['alpaca_secret_key'] = $secretKey;
        }
        if ($request->filled('alpaca_live_secret_key')) {
            $updateData['alpaca_live_secret_key'] = $liveSecretKey;
        }
        $user->update($updateData);

        // Clear connection cache keys so they are re-checked immediately
        cache()->forget("alpaca_conn_status_paper_{$user->id}");
        cache()->forget("alpaca_conn_status_live_{$user->id}");

        if ($connectionSuccess) {
            return Redirect::route('profile.edit')
                ->with('status', 'alpaca-updated-success');
        } else {
            return Redirect::route('profile.edit')
                ->with('status', 'alpaca-updated-error')
                ->with('alpaca_error_msg', $connectionMessage);
        }
    }

    /**
     * Update the user's bot trading strategy settings.
     */
    public function updateBotStrategy(Request $request): RedirectResponse
    {
        $request->validate([
            'bot_buy_threshold' => 'required|numeric',
            'bot_take_profit' => 'required|numeric|min:0',
            'bot_stop_loss' => 'required|numeric|max:0',
            'bot_order_size' => 'required|numeric|min:1',
            'bot_max_investment' => 'required|numeric|min:1',
            'daily_spend_limit' => 'nullable|numeric|min:0',
            'weekly_spend_limit' => 'nullable|numeric|min:0',
            'monthly_spend_limit' => 'nullable|numeric|min:0',

            'live_bot_buy_threshold' => 'required|numeric',
            'live_bot_take_profit' => 'required|numeric|min:0',
            'live_bot_stop_loss' => 'required|numeric|max:0',
            'live_bot_order_size' => 'required|numeric|min:1',
            'live_bot_max_investment' => 'required|numeric|min:1',
            'live_daily_spend_limit' => 'nullable|numeric|min:0',
            'live_weekly_spend_limit' => 'nullable|numeric|min:0',
            'live_monthly_spend_limit' => 'nullable|numeric|min:0',
        ]);

        $user = $request->user();
        
        $user->update([
            'bot_buy_threshold' => $request->input('bot_buy_threshold'),
            'bot_take_profit' => $request->input('bot_take_profit'),
            'bot_stop_loss' => $request->input('bot_stop_loss'),
            'bot_order_size' => $request->input('bot_order_size'),
            'bot_max_investment' => $request->input('bot_max_investment'),
            'daily_spend_limit' => $request->input('daily_spend_limit'),
            'weekly_spend_limit' => $request->input('weekly_spend_limit'),
            'monthly_spend_limit' => $request->input('monthly_spend_limit'),

            'live_bot_buy_threshold' => $request->input('live_bot_buy_threshold'),
            'live_bot_take_profit' => $request->input('live_bot_take_profit'),
            'live_bot_stop_loss' => $request->input('live_bot_stop_loss'),
            'live_bot_order_size' => $request->input('live_bot_order_size'),
            'live_bot_max_investment' => $request->input('live_bot_max_investment'),
            'live_daily_spend_limit' => $request->input('live_daily_spend_limit'),
            'live_weekly_spend_limit' => $request->input('live_weekly_spend_limit'),
            'live_monthly_spend_limit' => $request->input('live_monthly_spend_limit'),
        ]);

        return Redirect::route('profile.edit')->with('status', 'bot-strategy-updated');
    }

    /**
     * Update user risk profile dynamically.
     */
    public function updateRiskProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'risk_profile' => 'required|in:conservative,risky,extreme'
        ]);

        $user = $request->user();
        $profile = $request->input('risk_profile');

        if ($profile === 'conservative') {
            $user->update([
                'live_bot_buy_threshold' => -3.0,
                'live_bot_take_profit' => 1.2,
                'live_bot_stop_loss' => -1.5,
                'live_bot_order_size' => 100.0,
                'live_daily_spend_limit' => 200.0
            ]);
            return back()->with('success', 'Perfil de riesgo **Conservador** aplicado con éxito. Los límites han sido moderados para minimizar ganancias y pérdidas.');
        } elseif ($profile === 'risky') {
            $user->update([
                'live_bot_buy_threshold' => -0.5,
                'live_bot_take_profit' => 10.0,
                'live_bot_stop_loss' => -8.0,
                'live_bot_order_size' => 1000.0,
                'live_daily_spend_limit' => 3000.0
            ]);
            return back()->with('success', 'Perfil de riesgo **Arriesgado** aplicado con éxito. Ten en cuenta los riesgos de alta volatilidad asociados.');
        } elseif ($profile === 'extreme') {
            $user->update([
                'live_bot_buy_threshold' => -0.2,
                'live_bot_take_profit' => 20.0,
                'live_bot_stop_loss' => -15.0,
                'live_bot_order_size' => 2000.0,
                'live_daily_spend_limit' => 5000.0
            ]);
            return back()->with('success', 'Perfil de riesgo **Extremo** aplicado con éxito. ¡CUIDADO! Este perfil opera con márgenes muy amplios y alta exposición.');
        }

        return back();
    }
}
