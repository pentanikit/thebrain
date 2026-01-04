<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BulkSmsBdClient
{
    public function send(string $to, string $message): array
    {
        $cfg = config('services.bulksmsbd');

        if (empty($cfg['url']) || empty($cfg['api_key']) || empty($cfg['sender_id'])) {
            return ['ok' => false, 'error' => 'BulkSMSBD config missing.', 'response' => null];
        }

        $to = $this->normalizeBdNumber($to);
        $message = $this->sanitizeMessage($message);

        $response = Http::timeout(12)->asForm()->post($cfg['url'], [
            'api_key'  => $cfg['api_key'],
            'senderid' => $cfg['sender_id'],
            'number'   => $to,
            'message'  => $message,
        ]);

        return [
            'ok'       => $response->successful(),
            'status'   => $response->status(),
            'response' => $response->body(),
        ];
    }

    private function normalizeBdNumber(string $to): string
    {
        $to = preg_replace('/\s+/', '', trim($to));
        $to = ltrim($to, '+');

        // 01XXXXXXXXX → 8801XXXXXXXXX
        if (Str::startsWith($to, '01') && strlen($to) === 11) return '88' . $to;

        // 8801XXXXXXXXX → OK
        if (Str::startsWith($to, '8801')) return $to;

        return $to; // fallback
    }

    private function sanitizeMessage(string $message): string
    {
        $message = trim($message);
        $message = preg_replace("/[^\P{C}\n]+/u", "", $message);
        return Str::limit($message, 300, '');
    }
}
