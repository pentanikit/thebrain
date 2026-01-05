<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\BulkSmsBdClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderMessege implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(
        public int $orderId,
        public string $type = 'placed' // placed|paid|shipped etc (optional)
    ) {}

    public function handle(BulkSmsBdClient $sms): void
    {
        $order = Order::with('items')->find($this->orderId);
        if (!$order) return;

        $phone = '01677497966';
        if (!$phone) return;

        $message = $this->buildMessage($order);

        $res = $sms->send($phone, $message);

        Log::info('Order SMS attempt', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            // 'to' => '01827400100',
            'to' => $phone,
            'ok' => $res['ok'] ?? false,
            'status' => $res['status'] ?? null,
            'response' => $res['response'] ?? null,
            'type' => $this->type,
        ]);

        if (empty($res['ok'])) {
            throw new \RuntimeException('BulkSMSBD send failed: ' . ($res['response'] ?? 'unknown'));
        }
    }

    private function buildMessage(Order $order): string
    {
        $orderNo = $order->order_number ?: ('#' . $order->id);
        $total   = $this->money($order->total);
        $status  = strtoupper((string) $order->status);
        $pay     = strtoupper((string) $order->payment_status);

        $customerName  = trim((string) ($order->customer_name ?? ''));
        $customerPhone = trim((string) ($order->customer_phone ?? ''));
        $shipAddr      = trim((string) ($order->shipping_address ?? ''));
        $shipCity      = trim((string) ($order->shipping_city ?? ''));
        $shipPostcode  = trim((string) ($order->shipping_postcode ?? ''));

        // Total quantity (sum of all item quantities)
        $totalQty = (int) $order->items->sum(fn($i) => (int) $i->quantity);

        // Items summary (keep short): "ProductA x1, ProductB x2 +N more"
        $items = $order->items
            ->take(2)
            ->map(fn($i) => $this->short((string) $i->product_name, 16) . ' x' . (int) $i->quantity)
            ->implode(', ');

        $moreCount = max(0, $order->items->count() - 2);
        if ($moreCount > 0) $items .= " +{$moreCount} more";

        // Compact shipping line
        $shippingLine = trim(implode(', ', array_filter([
            $shipAddr,
            $shipCity,
            $shipPostcode ? "Post: {$shipPostcode}" : null,
        ])));

        // Short, formal, full summary
        return
            "Order Confirmed ✅\n" .
            "Order: {$orderNo}\n" .
            "Customer: " . ($customerName ?: "N/A") . ($customerPhone ? " ({$customerPhone})" : "") . "\n" .
            "Ship To: " . ($shippingLine ?: "N/A") . "\n" .
            "Items: {$items}\n" .
            "Qty: {$totalQty} | Total: {$total}\n" .
            "Payment: {$pay} | Status: {$status}\n" .
            "Thank you.";
    }


    private function money($amount): string
    {
        if ($amount === null || $amount === '') return 'BDT 0';
        return 'BDT ' . number_format((float)$amount, 0);
    }

    private function short(string $text, int $max): string
    {
        $text = trim($text);
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max - 1) . '…' : $text;
    }
}
