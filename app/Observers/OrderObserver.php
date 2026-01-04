<?php

namespace App\Observers;

use App\Jobs\SendOrderSmsJob;
use App\Models\Order;

class OrderObserver
{
    public function created(Order $order): void
    {
        // Send on order placed
        SendOrderSmsJob::dispatch($order->id, 'placed')->onQueue('sms');
    }

    public function updated(Order $order): void
    {
        // Optional: send when payment becomes paid
        if ($order->isDirty('payment_status') && strtolower((string)$order->payment_status) === 'paid') {
            SendOrderSmsJob::dispatch($order->id, 'paid')->onQueue('sms');
        }
    }
}
