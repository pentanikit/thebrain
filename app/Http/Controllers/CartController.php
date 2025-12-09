<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Active cart for current user / guest.
     */
    protected function getCurrentCart(Request $request): Cart
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

        // always load items + product
        $cart->load('items.product');

        return $cart;
    }

    /**
     * Full cart page (optional, for /cart).
     */
    public function index(Request $request)
    {
        $cart = $this->getCurrentCart($request);

        return view('frontend.cart.index', [
            'cart'  => $cart,
            'items' => $cart->items,
        ]);
    }

    /**
     * Mini-cart data for header dropdown (if you want to call via AJAX or direct include).
     */
    public function mini(Request $request)
    {
        $cart = $this->getCurrentCart($request);

        return view('frontend.partials.cart-dropdown', [
            'cart'  => $cart,
            'items' => $cart->items,
        ]);
    }

    /**
     * Add product to cart.
     */
    public function add(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $quantity   = (int) ($request->quantity ?? 1);
        $cart       = $this->getCurrentCart($request);

        // choose price (offer_price priority)
        $unitPrice = $product->offer_price && $product->offer_price < $product->price
            ? $product->offer_price
            : $product->price;

        // existing item?
        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            $item->quantity    += $quantity;
            $item->total_price = $item->quantity * $item->unit_price;
            $item->save();
        } else {
            $item = $cart->items()->create([
                'product_id'  => $product->id,
                'quantity'    => $quantity,
                'unit_price'  => $unitPrice,
                'total_price' => $unitPrice * $quantity,
            ]);
        }

        // recalc totals
        $cart->load('items');
        $cart->recalculateTotals();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Product added to cart.',
                'cart_total_qty' => $cart->totalQuantity(),
                'cart_total' => $cart->total,
            ]);
        }

        return back()->with('success', 'Product added to cart.');
    }

    /**
     * Update quantity of a specific cart item.
     */
    public function update(Request $request, CartItem $item)
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $quantity = (int) $request->quantity;

        $item->quantity    = $quantity;
        $item->total_price = $quantity * $item->unit_price;
        $item->save();

        $cart = $item->cart;
        $cart->load('items');
        $cart->recalculateTotals();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Cart updated.',
                'cart_total_qty' => $cart->totalQuantity(),
                'cart_total' => $cart->total,
            ]);
        }

        return back()->with('success', 'Cart updated.');
    }

    /**
     * Remove one item.
     */
    public function remove(Request $request, CartItem $item)
    {
        $cart = $item->cart;
        $item->delete();

        $cart->load('items');
        $cart->recalculateTotals();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Item removed.',
                'cart_total_qty' => $cart->totalQuantity(),
                'cart_total' => $cart->total,
            ]);
        }

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * Clear whole cart.
     */
    public function clear(Request $request)
    {
        $cart = $this->getCurrentCart($request);
        $cart->items()->delete();
        $cart->recalculateTotals();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Cart cleared.']);
        }

        return back()->with('success', 'Cart cleared.');
    }
}
