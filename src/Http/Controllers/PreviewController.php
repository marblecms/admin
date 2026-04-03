<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Facades\Marble;
use Marble\Admin\Models\Item;

class PreviewController extends Controller
{
    use AuthorizesRequests;

    /**
     * Generate or refresh the preview token and return the preview URL.
     */
    public function generate(Item $item)
    {
        $this->authorize('update', $item);
        $token = $item->generatePreviewToken();
        $frontendUrl = rtrim(config('marble.frontend_url', ''), '/');

        return redirect()->route('marble.item.edit', $item)
            ->with('preview_url', $frontendUrl . '/marble-preview/' . $token);
    }

    /**
     * Serve a draft item for preview. Public route — no admin auth required.
     */
    public function show(string $token)
    {
        $item = Item::where('preview_token', $token)
            ->with('blueprint')
            ->first();

        if (!$item) {
            abort(404);
        }

        $view = Marble::viewFor($item);

        return view($view, compact('item'));
    }
}
