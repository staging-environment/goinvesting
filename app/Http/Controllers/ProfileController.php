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
            'alpaca_is_paper' => 'nullable|boolean'
        ]);

        $user = $request->user();
        
        $user->update([
            'alpaca_key_id' => $request->input('alpaca_key_id'),
            'alpaca_secret_key' => $request->input('alpaca_secret_key'),
            'alpaca_account_id' => $request->input('alpaca_account_id'),
            'alpaca_is_paper' => $request->has('alpaca_is_paper') ? (bool)$request->input('alpaca_is_paper') : false
        ]);

        return Redirect::route('profile.edit')->with('status', 'alpaca-updated');
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
        ]);

        $user = $request->user();
        
        $user->update([
            'bot_buy_threshold' => $request->input('bot_buy_threshold'),
            'bot_take_profit' => $request->input('bot_take_profit'),
            'bot_stop_loss' => $request->input('bot_stop_loss'),
            'bot_order_size' => $request->input('bot_order_size'),
            'bot_max_investment' => $request->input('bot_max_investment'),
        ]);

        return Redirect::route('profile.edit')->with('status', 'bot-strategy-updated');
    }
}

