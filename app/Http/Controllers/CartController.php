<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CartItems;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    protected function getCurrentCart(Request $request)
    {
        return $this->cartService->getCurrentCart($request);
    }

    public function index(Request $request)
    {
        $cart  = $this->getCurrentCart($request);
        $items = $cart->items;

        return view('frontend.pages.cart', compact('cart', 'items'));
    }

    public function mini(Request $request)
    {
        $cart  = $this->getCurrentCart($request);
        $items = $cart->items;

        return view('frontend.partials.cart-dropdown', compact('cart', 'items'));
    }

    public function add(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $quantity = (int) ($request->quantity ?? 1);
        $cart     = $this->getCurrentCart($request);

        $unitPrice = $product->offer_price && $product->offer_price < $product->price
            ? $product->offer_price
            : $product->price;

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

        $cart->load('items');
        $cart->recalculateTotals();

        if ($request->wantsJson()) {
            return response()->json([
                'message'        => 'Product added to cart.',
                'cart_total_qty' => $cart->totalQuantity(),
                'cart_total'     => $cart->total,
            ]);
        }

        return redirect()->route('cart.showcart')->with('success', 'Product added to cart.');
    }

    public function update(Request $request, CartItem $item)
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $quantity           = (int) $request->quantity;
        $item->quantity     = $quantity;
        $item->total_price  = $quantity * $item->unit_price;
        $item->save();

        $cart = $item->cart;
        $cart->load('items');
        $cart->recalculateTotals();

        if ($request->wantsJson()) {
            return response()->json([
                'message'        => 'Cart updated.',
                'cart_total_qty' => $cart->totalQuantity(),
                'cart_total'     => $cart->total,
            ]);
        }

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Request $request, CartItems $item)
    {
        $cart = $item->cart;

        $item->delete();

        $cart->load('items');
        $cart->recalculateTotals();

        if ($request->wantsJson()) {
            return response()->json([
                'message'        => 'Item removed.',
                'cart_total_qty' => $cart->totalQuantity(),
                'cart_total'     => $cart->total,
            ]);
        }

        return back()->with('success', 'Item removed from cart.');
    }

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
