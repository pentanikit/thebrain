<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItems;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

public function store(Request $request)
{
    $traceId = (string) Str::uuid();
    $t0 = microtime(true);

    // IMPORTANT: never dump full request (privacy). Mask sensitive bits.
    Log::info('[ORDER_STORE] START', [
        'trace_id' => $traceId,
        'ip'       => $request->ip(),
        'ua'       => substr((string)$request->userAgent(), 0, 120),
        'url'      => $request->fullUrl(),
        'method'   => $request->method(),
        'session'  => substr((string)session()->getId(), 0, 12),
    ]);

    try {
        Log::info('[ORDER_STORE] Validating request...', ['trace_id' => $traceId]);

        $data = $request->validate([
            'customer_name'      => 'required|string|max:255',
            'customer_phone'     => 'required|string|max:30',
            'customer_email'     => 'nullable|email|max:255',

            'shipping_address'   => 'required|string',
            'shipping_city'      => 'nullable|string|max:100',
            'shipping_postcode'  => 'nullable|string|max:30',

            'notes'              => 'nullable|string',
            'payment_method'     => 'required|string|max:50',
            'delivery_area'      => 'required|in:inside_dhaka,outside_dhaka',

            'frontend_subtotal'  => 'nullable|numeric|min:0',
            'shipping_charge'    => 'nullable|numeric|min:0',
            'payable_amount'     => 'nullable|numeric|min:0',
        ]);

        Log::info('[ORDER_STORE] Validation passed', [
            'trace_id'        => $traceId,
            'payment_method'  => $data['payment_method'] ?? null,
            'delivery_area'   => $data['delivery_area'] ?? null,
            'customer_phone'  => $data['customer_phone'] ?? null,
        ]);

        Log::info('[ORDER_STORE] Fetching cart...', ['trace_id' => $traceId]);
        $cart = $this->getCurrentCart($request);

        if (!$cart) {
            Log::warning('[ORDER_STORE] Cart not found', ['trace_id' => $traceId]);
            return redirect()->route('cart.showcart')->with('error', 'Your cart is empty.');
        }

        // load relations
        $cart->load('items.product');

        Log::info('[ORDER_STORE] Cart loaded', [
            'trace_id'    => $traceId,
            'cart_id'     => $cart->id ?? null,
            'items_count' => $cart->items?->count() ?? 0,
        ]);

        if ($cart->items->isEmpty()) {
            Log::warning('[ORDER_STORE] Cart items empty', [
                'trace_id' => $traceId,
                'cart_id'  => $cart->id ?? null,
            ]);
            return redirect()->route('cart.showcart')->with('error', 'Your cart is empty.');
        }

        // Shipping calculation logging
        $insideCharge  = site_setting('shipping_charge_inside_dhaka', 0);
        $outsideCharge = site_setting('shipping_charge_outside_dhaka', 0);
        $shippingCost  = ($data['delivery_area'] === 'outside_dhaka') ? $outsideCharge : $insideCharge;

        Log::info('[ORDER_STORE] Shipping resolved', [
            'trace_id'      => $traceId,
            'inside_charge' => $insideCharge,
            'outside_charge'=> $outsideCharge,
            'shipping_cost' => $shippingCost,
        ]);

        // =======================
        // TRANSACTION START
        // =======================
        Log::info('[ORDER_STORE] Starting DB transaction...', ['trace_id' => $traceId]);

        $order = DB::transaction(function () use ($data, $cart, $shippingCost, $traceId) {

            $txStart = microtime(true);

            Log::info('[ORDER_STORE][TX] Building product id list...', [
                'trace_id' => $traceId,
                'cart_id'  => $cart->id ?? null,
            ]);

            $productIds = $cart->items
                ->pluck('product_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            Log::info('[ORDER_STORE][TX] Product IDs extracted', [
                'trace_id'    => $traceId,
                'product_ids' => $productIds,
            ]);

            if (empty($productIds)) {
                Log::error('[ORDER_STORE][TX] Cart products are missing (no product_ids)', [
                    'trace_id' => $traceId,
                    'cart_id'  => $cart->id ?? null,
                ]);
                throw new \Exception('Cart products are missing.');
            }

            Log::info('[ORDER_STORE][TX] Locking products FOR UPDATE...', [
                'trace_id' => $traceId,
                'count'    => count($productIds),
            ]);

            $lockT0 = microtime(true);

            $lockedProducts = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            Log::info('[ORDER_STORE][TX] Products locked', [
                'trace_id'      => $traceId,
                'locked_count'  => $lockedProducts->count(),
                'lock_time_ms'  => round((microtime(true) - $lockT0) * 1000, 2),
            ]);

            // ========= STOCK VALIDATION =========
            Log::info('[ORDER_STORE][TX] Validating stock...', ['trace_id' => $traceId]);

            $stockErrors = [];

            foreach ($cart->items as $cartItem) {
                $pid = $cartItem->product_id;
                $qty = (int) ($cartItem->quantity ?? 1);
                if ($qty < 1) $qty = 1;

                $product = $lockedProducts->get($pid);

                Log::debug('[ORDER_STORE][TX] Checking item', [
                    'trace_id'   => $traceId,
                    'product_id' => $pid,
                    'qty'        => $qty,
                    'found'      => (bool)$product,
                ]);

                if (!$product) {
                    $stockErrors[] = "A product in your cart is no longer available.";
                    continue;
                }

                if (isset($product->is_active) && (int)$product->is_active !== 1) {
                    $stockErrors[] = "{$product->name} is currently unavailable.";
                    continue;
                }

                $currentStock = (int) ($product->stock_quantity ?? 0);

                if ($currentStock < $qty) {
                    $stockErrors[] = "{$product->name} has only {$currentStock} left.";
                }
            }

            if (!empty($stockErrors)) {
                Log::warning('[ORDER_STORE][TX] Stock validation failed', [
                    'trace_id' => $traceId,
                    'errors'   => $stockErrors,
                ]);
                throw new \Exception(implode(' ', $stockErrors));
            }

            Log::info('[ORDER_STORE][TX] Stock OK', ['trace_id' => $traceId]);

            // ========= TOTALS =========
            Log::info('[ORDER_STORE][TX] Calculating totals...', ['trace_id' => $traceId]);

            $subtotal = 0;

            foreach ($cart->items as $cartItem) {
                $product = $lockedProducts->get($cartItem->product_id);

                $unitPrice = $cartItem->price ?? ($product->offer_price ?? $product->price ?? 0);
                $qty       = (int) ($cartItem->quantity ?? 1);
                if ($qty < 1) $qty = 1;

                $line = ((float) $unitPrice * $qty);
                $subtotal += $line;

                Log::debug('[ORDER_STORE][TX] Line calculated', [
                    'trace_id'   => $traceId,
                    'product_id' => $cartItem->product_id,
                    'unit_price' => $unitPrice,
                    'qty'        => $qty,
                    'line_total' => $line,
                ]);
            }

            $discount = 0;
            $total    = $subtotal - $discount + (float) $shippingCost;

            Log::info('[ORDER_STORE][TX] Totals computed', [
                'trace_id'      => $traceId,
                'subtotal'      => $subtotal,
                'discount'      => $discount,
                'shipping_cost' => $shippingCost,
                'total'         => $total,
            ]);

            // ========= CREATE ORDER =========
            $orderNumber = $this->generateOrderNumber();

            Log::info('[ORDER_STORE][TX] Creating order...', [
                'trace_id'     => $traceId,
                'order_number' => $orderNumber,
            ]);

            $order = Order::create([
                'order_number'      => $orderNumber,

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

            Log::info('[ORDER_STORE][TX] Order created', [
                'trace_id'  => $traceId,
                'order_id'  => $order->id,
            ]);

            // ========= CREATE ITEMS + UPDATE INVENTORY =========
            Log::info('[ORDER_STORE][TX] Creating order items + updating stock...', [
                'trace_id' => $traceId,
                'items'    => $cart->items->count(),
            ]);

            foreach ($cart->items as $cartItem) {
                $product = $lockedProducts->get($cartItem->product_id);

                $unitPrice = $cartItem->price ?? ($product->offer_price ?? $product->price ?? 0);
                $qty       = (int) ($cartItem->quantity ?? 1);
                if ($qty < 1) $qty = 1;

                $rowTotal  = (float) $unitPrice * $qty;

                $oi = OrderItems::create([
                    'order_id'      => $order->id,
                    'product_id'    => $product->id,
                    'product_name'  => $product->name ?? 'Product',
                    'product_sku'   => $product->sku ?? null,
                    'quantity'      => $qty,
                    'unit_price'    => $unitPrice,
                    'total_price'   => $rowTotal,
                ]);

                Log::debug('[ORDER_STORE][TX] Order item created', [
                    'trace_id'      => $traceId,
                    'order_item_id' => $oi->id ?? null,
                    'product_id'    => $product->id,
                    'qty'           => $qty,
                    'unit_price'    => $unitPrice,
                ]);

                $oldStock = (int) $product->stock_quantity;
                $newStock = $oldStock - $qty;
                if ($newStock < 0) $newStock = 0;

                $product->stock_quantity = $newStock;
                $product->stock_status   = ($newStock > 0) ? 'in_stock' : 'out_of_stock';
                $product->save();

                Log::info('[ORDER_STORE][TX] Stock updated', [
                    'trace_id'     => $traceId,
                    'product_id'   => $product->id,
                    'old_stock'    => $oldStock,
                    'new_stock'    => $newStock,
                    'stock_status' => $product->stock_status,
                ]);
            }

            // ========= CLEAR CART =========
            Log::info('[ORDER_STORE][TX] Clearing cart...', [
                'trace_id' => $traceId,
                'cart_id'  => $cart->id ?? null,
            ]);

            $deletedItems = $cart->items()->delete();
            $deletedCart  = $cart->delete();

            Log::info('[ORDER_STORE][TX] Cart cleared', [
                'trace_id'       => $traceId,
                'deleted_items'  => $deletedItems,
                'deleted_cart'   => (bool)$deletedCart,
                'tx_time_ms'     => round((microtime(true) - $txStart) * 1000, 2),
            ]);

            return $order;

        }, 3);

        Log::info('[ORDER_STORE] SUCCESS', [
            'trace_id'   => $traceId,
            'order_id'   => $order->id ?? null,
            'order_no'   => $order->order_number ?? null,
            'time_ms'    => round((microtime(true) - $t0) * 1000, 2),
        ]);

        return redirect()->route('orders.thankyou', $order->order_number);

    } catch (\Throwable $e) {

        // This is the missing piece: log message + location + trace
        Log::error('[ORDER_STORE] FAILED', [
            'trace_id' => $traceId,
            'message'  => $e->getMessage(),
            'class'    => get_class($e),
            'file'     => $e->getFile(),
            'line'     => $e->getLine(),
            'time_ms'  => round((microtime(true) - $t0) * 1000, 2),
            'trace'    => substr($e->getTraceAsString(), 0, 5000), // prevent huge logs
        ]);

        return redirect()
            ->route('cart.showcart')
            ->with('error', $e->getMessage() ?: 'Order failed. Please try again.');
    }
}



    public function thankYou($orderNumber)
    {
        $order = Order::with('items.product')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return view('frontend.pages.thank-you', compact('order'));
    }


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
        return 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
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

        //  Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        //  Date filter
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
