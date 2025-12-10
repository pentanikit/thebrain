<?php

namespace App\Services;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartService
{
    /**
     * Get or create current active cart for logged-in user or guest.
     */
    public function getCurrentCart(Request $request): Cart
    {
        $sessionId = $request->session()->getId();

        if (Auth::check()) {
            $cart = Cart::firstOrCreate(
                ['user_id' => Auth::id(), 'status' => 'active'],
                ['session_id' => $sessionId]
            );
        } else {
            $cart = Cart::firstOrCreate(
                ['session_id' => $sessionId, 'status' => 'active'],
                ['user_id' => null]
            );
        }

        // Always load relationships for dropdown
        $cart->load(['items.product']);

        // Make sure totals are not stale
        $cart->recalculateTotals();

        return $cart;
    }
}
