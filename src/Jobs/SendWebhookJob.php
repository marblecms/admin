<?php

namespace Marble\Admin\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Marble\Admin\Models\Webhook;

class SendWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public readonly Webhook $webhook,
        public readonly array $payload,
    ) {}

    public function handle(): void
    {
        $headers = ['Content-Type' => 'application/json'];

        if ($this->webhook->secret) {
            $signature = hash_hmac('sha256', json_encode($this->payload), $this->webhook->secret);
            $headers['X-Marble-Signature'] = $signature;
        }

        $response = Http::timeout(10)
            ->withHeaders($headers)
            ->post($this->webhook->url, $this->payload);

        if (!$response->successful()) {
            Log::warning("Marble webhook [{$this->webhook->name}] returned HTTP {$response->status()}");
            $this->release($this->backoff);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("Marble webhook [{$this->webhook->name}] permanently failed: " . $e->getMessage());
    }
}
