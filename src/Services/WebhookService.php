<?php

namespace Marble\Admin\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\Webhook;

class WebhookService
{
    /**
     * Fire a webhook event for an item.
     *
     * @param  array  $changedFields  Optional map of field identifier => ['old' => ..., 'new' => ...]
     */
    public function fire(string $event, Item $item, array $changedFields = []): void
    {
        $webhooks = Webhook::where('active', true)->get()
            ->filter(fn ($w) => $w->listensTo($event));

        if ($webhooks->isEmpty()) {
            return;
        }

        $item->loadMissing('blueprint');

        $workflowStep = null;
        if ($item->current_workflow_step_id) {
            $item->loadMissing('workflowStep');
            $workflowStep = $item->workflowStep?->name;
        } elseif ($item->status === 'published') {
            $workflowStep = 'published';
        }

        $payload = [
            'event'    => $event,
            'fired_at' => now()->toIso8601String(),
            'item'     => [
                'id'             => $item->id,
                'name'           => $item->name(),
                'blueprint'      => $item->blueprint->identifier,
                'blueprint_name' => $item->blueprint->name,
                'status'         => $item->status,
                'workflow_step'  => $workflowStep,
                'slugs'          => $item->allSlugs(),
            ],
        ];

        if (!empty($changedFields)) {
            $payload['changed_fields'] = $changedFields;
        }

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
