<?php

namespace App\Http\Controllers;

use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchlistController extends Controller
{
    /**
     * Add symbol to user watchlist.
     */
    public function store(Request $request)
    {
        $request->validate([
            'symbol' => 'required|string',
            'name' => 'nullable|string'
        ]);

        $symbol = strtoupper($request->input('symbol'));

        Auth::user()->watchlists()->firstOrCreate(
            ['symbol' => $symbol],
            ['name' => $request->input('name')]
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Added to watchlist']);
        }

        return redirect()->back()->with('success', 'Added to watchlist');
    }

    /**
     * Remove symbol from user watchlist.
     */
    public function destroy(string $symbol)
    {
        $symbol = strtoupper($symbol);

        Auth::user()->watchlists()->where('symbol', $symbol)->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Removed from watchlist']);
        }

        return redirect()->back()->with('success', 'Removed from watchlist');
    }
}
