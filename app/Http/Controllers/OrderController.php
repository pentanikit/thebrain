<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Handle order placement from cart (POST).
     */
    public function store(Request $request)
    {
        // Basic checkout validation – adjust as needed
        $data = $request->validate([
            'customer_name'      => 'required|string|max:255',
            'customer_phone'     => 'required|string|max:30',
            'customer_email'     => 'nullable|email|max:255',
            'shipping_address'   => 'required|string',
            'shipping_city'      => 'nullable|string|max:100',
            'shipping_postcode'  => 'nullable|string|max:30',
            'notes'              => 'nullable|string',
            'payment_method'     => 'required|string|max:50', // e.g. cod, bkash, card
        ]);

        // Get current cart with items
        $cart = $this->getCurrentCart($request);

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()
                ->route('cart.showcart')
                ->with('error', 'Your cart is empty.');
        }

        // Create order in a transaction
        $order = DB::transaction(function () use ($data, $cart, $request) {
            // Calculate subtotal from cart
            $subtotal = 0;

            foreach ($cart->items as $cartItem) {
                $unitPrice = $cartItem->price ?? ($cartItem->product->sale_price ?? $cartItem->product->price ?? 0);
                $subtotal += $unitPrice * $cartItem->quantity;
            }

            $shippingCost = 0;   // you can set some logic later
            $discount     = 0;   // coupon logic etc
            $total        = $subtotal - $discount + $shippingCost;

            $order = Order::create([
                'user_id'          => auth()->id(),
                'order_number'     => $this->generateOrderNumber(),
                'customer_name'    => $data['customer_name'],
                'customer_phone'   => $data['customer_phone'],
                'customer_email'   => $data['customer_email'] ?? null,
                'shipping_address' => $data['shipping_address'],
                'shipping_city'    => $data['shipping_city'] ?? null,
                'shipping_postcode'=> $data['shipping_postcode'] ?? null,
                'subtotal'         => $subtotal,
                'discount'         => $discount,
                'shipping_cost'    => $shippingCost,
                'total'            => $total,
                'status'           => 'pending',
                'payment_method'   => $data['payment_method'],
                'payment_status'   => 'unpaid',
                'notes'            => $data['notes'] ?? null,
            ]);

            // Create order items from cart items
            foreach ($cart->items as $cartItem) {
                $product   = $cartItem->product;
                $unitPrice = $cartItem->price ?? ($product->sale_price ?? $product->price ?? 0);
                $qty       = $cartItem->quantity;
                $rowTotal  = $unitPrice * $qty;

                OrderItems::create([
                    'order_id'     => $order->id,
                    'product_id'   => $product->id ?? null,
                    'product_name' => $product->name ?? 'Product',
                    'product_sku'  => $product->sku ?? null,
                    'quantity'     => $qty,
                    'unit_price'   => $unitPrice,
                    'total_price'  => $rowTotal,
                ]);
            }

            // Clear cart after order placed
            $cart->items()->delete();
            $cart->delete();

            return $order;
        });

        // Redirect to thank you page with order_number
        return redirect()->route('orders.thankyou', $order->order_number);
    }

    /**
     * Thank you page showing order details.
     */
    public function thankYou($orderNumber)
    {
        $order = Order::with('items.product')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return view('frontend.pages.thank-you', compact('order'));
    }

    /**
     * Get current cart (session-based).
     * Adjust to match your existing Cart implementation if needed.
     */
    protected function getCurrentCart(Request $request): ?Cart
    {
        $sessionId = $request->session()->getId();

        return Cart::with(['items.product'])
            ->where('session_id', $sessionId)
            ->first();
    }

    /**
     * Generate a unique order number like PONN-20251210-ABC123
     */
    protected function generateOrderNumber(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}
