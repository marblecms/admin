<?php

namespace Marble\Admin\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\Webhook;

class WebhookService
{
    public function fire(string $event, Item $item): void
    {
        $webhooks = Webhook::where('active', true)->get()
            ->filter(fn ($w) => $w->listensTo($event));

        if ($webhooks->isEmpty()) {
            return;
        }

        $payload = [
            'event'      => $event,
            'fired_at'   => now()->toIso8601String(),
            'item' => [
                'id'        => $item->id,
                'name'      => $item->name(),
                'blueprint' => $item->blueprint->identifier,
                'status'    => $item->status,
                'slug'      => $item->slug(),
            ],
        ];

        foreach ($webhooks as $webhook) {
            $this->dispatch($webhook, $payload);
        }
    }

    protected function dispatch(Webhook $webhook, array $payload): void
    {
        try {
            $request = Http::timeout(5)->withHeaders($this->headers($webhook, $payload));
            $request->post($webhook->url, $payload);
        } catch (\Throwable $e) {
            Log::warning("Marble webhook [{$webhook->name}] failed: " . $e->getMessage());
        }
    }

    protected function headers(Webhook $webhook, array $payload): array
    {
        $headers = ['Content-Type' => 'application/json'];

        if ($webhook->secret) {
            $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret);
            $headers['X-Marble-Signature'] = $signature;
        }

        return $headers;
    }
}
