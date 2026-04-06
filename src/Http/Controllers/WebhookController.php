<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\Webhook;

class WebhookController extends Controller
{
    public function index()
    {
        return view('marble::webhook.index', [
            'webhooks' => Webhook::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('marble::webhook.edit', ['webhook' => null]);
    }

    public function edit(Webhook $webhook)
    {
        return view('marble::webhook.edit', compact('webhook'));
    }

    public function save(Request $request, ?Webhook $webhook = null)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'url'    => 'required|url|max:500',
            'events' => 'nullable|array',
            'secret' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
        ]);

        $data['events'] = $data['events'] ?? [];
        $data['active'] = $request->boolean('active');

        if ($webhook) {
            $webhook->update($data);
        } else {
            Webhook::create($data);
        }

        return redirect()->route('marble.webhook.index')
            ->with('success', trans('marble::admin.webhook_saved'));
    }

    public function delete(Webhook $webhook)
    {
        $webhook->delete();
        return redirect()->route('marble.webhook.index')
            ->with('success', trans('marble::admin.webhook_deleted'));
    }
}
