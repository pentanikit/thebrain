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
    // Updated validation for delivery_area (from cart modal)
    $data = $request->validate([
        'customer_name'      => 'required|string|max:255',
        'customer_phone'     => 'required|string|max:30',
        'customer_email'     => 'nullable|email|max:255',

        'shipping_address'   => 'required|string',
        'shipping_city'      => 'nullable|string|max:100',
        'shipping_postcode'  => 'nullable|string|max:30',

        'notes'              => 'nullable|string',
        'payment_method'     => 'required|string|max:50', // cod, bkash, card

        // from updated cart modal
        'delivery_area'      => 'required|in:inside_dhaka,outside_dhaka',

        // frontend calculated values (DO NOT TRUST — optional)
        'frontend_subtotal'  => 'nullable|numeric|min:0',
        'shipping_charge'    => 'nullable|numeric|min:0',
        'payable_amount'     => 'nullable|numeric|min:0',
    ]);

    // Get current cart with items
    $cart = $this->getCurrentCart($request);

    if (!$cart || $cart->items->isEmpty()) {
        return redirect()
            ->route('cart.showcart')
            ->with('error', 'Your cart is empty.');
    }

    // ===== SAFE site_setting() wrapper (missing key / null / throws => fallback) =====
    // $safe_setting = function ($key, $default = 0) {
    //     try {
    //         $val = site_setting($key);
    //         if ($val === null || $val === '') return $default;
    //         return is_numeric($val) ? (float) $val : $default;
    //     } catch (\Throwable $e) {
    //         return $default;
    //     }
    // };

    $insideCharge  = site_setting('shipping_charge_inside_dhaka', 0);
    $outsideCharge = site_setting('shipping_charge_outside_dhaka', 0);

    // Decide shipping cost based on delivery_area
    $shippingCost = ($data['delivery_area'] === 'outside_dhaka') ? $outsideCharge : $insideCharge;

    // Create order in a transaction
    $order = DB::transaction(function () use ($data, $cart, $shippingCost) {

        // Calculate subtotal from cart (trusted)
        $subtotal = 0;

        foreach ($cart->items as $cartItem) {
            $product   = $cartItem->product;

            $unitPrice = $cartItem->price ?? ($product->sale_price ?? $product->price ?? 0);
            $qty       = (int) ($cartItem->quantity ?? 1);

            $subtotal += ((float) $unitPrice * $qty);
        }

        $discount = 0; // coupon logic later
        $total    = $subtotal - $discount + (float) $shippingCost;

        // Create order (ONLY columns that exist in your migration)
        $order = Order::create([
            // 'user_id'           => auth()->id(),
            'order_number'      => $this->generateOrderNumber(),

            'customer_name'     => $data['customer_name'],
            'customer_phone'    => $data['customer_phone'],
            'customer_email'    => $data['customer_email'] ?? null,

            'shipping_address'  => $data['shipping_address'],
            'shipping_city'     => $data['shipping_city'] ?? null,
            'shipping_postcode' => $data['shipping_postcode'] ?? null,

            'subtotal'          => $subtotal,
            'discount'          => $discount,
            'shipping_cost'     => $shippingCost,
            'total'             => $total,

            'status'            => 'pending',
            'payment_method'    => $data['payment_method'],
            'payment_status'    => 'unpaid',
            'notes'             => $data['notes'] ?? null,
        ]);

        // Create order items from cart items
        foreach ($cart->items as $cartItem) {
            $product   = $cartItem->product;
            $unitPrice = $cartItem->price ?? ($product->sale_price ?? $product->price ?? 0);
            $qty       = (int) ($cartItem->quantity ?? 1);
            $rowTotal  = (float) $unitPrice * $qty;

            OrderItems::create([
                'order_id'      => $order->id,
                'product_id'    => $product->id ?? null,
                'product_name'  => $product->name ?? 'Product',
                'product_sku'   => $product->sku ?? null,
                'quantity'      => $qty,
                'unit_price'    => $unitPrice,
                'total_price'   => $rowTotal,
            ]);
        }

        // Clear cart after order placed
        $cart->items()->delete();
        $cart->delete();

        return $order;
    });

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



    public function adminIndex(Request $request)
    {
        $query = Order::query();

        // 🔍 Search (order number / phone)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                ->orWhere('customer_phone', 'like', '%' . $request->search . '%');
            });
        }

        // 📌 Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 📅 Date filter
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('backend.orders.orders', compact('orders'));
    }

    public function adminShow(Order $order)
    {
        return view('backend.orders.orderdetails', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Order status updated.');
    }




}
