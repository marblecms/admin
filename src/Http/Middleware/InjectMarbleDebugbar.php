<?php

namespace Marble\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Facades\Marble;
use Marble\Admin\MarbleDebugbarContext;

class InjectMarbleDebugbar
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!config('marble.debugbar', false)) {
            return $response;
        }

        if (!Auth::guard('marble')->check()) {
            return $response;
        }

        // Only inject on frontend pages, not in the admin UI
        $adminPrefix = trim(config('marble.route_prefix', 'admin'), '/');
        if ($request->is($adminPrefix) || $request->is($adminPrefix . '/*')) {
            return $response;
        }

        // Only inject into HTML responses
        if (!($response instanceof Response)) {
            return $response;
        }

        $contentType = $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if (!str_contains($content, '</body>')) {
            return $response;
        }

        $html = $this->buildPanel();
        $content = str_replace('</body>', $html . '</body>', $content);
        $response->setContent($content);

        return $response;
    }

    private function buildPanel(): string
    {
        $ctx      = MarbleDebugbarContext::get();
        $item     = $ctx['item'] ?? null;
        $language = $ctx['language'] ?? null;
        $site     = $ctx['site'] ?? null;
        $prefix   = config('marble.route_prefix', 'admin');

        $rows = [];

        if ($item) {
            $editUrl = url("{$prefix}/item/edit/{$item->id}");
            $rows[] = ['label' => 'Item',      'value' => "#{$item->id} &nbsp;<strong>" . e($item->name()) . "</strong>"];
            $rows[] = ['label' => 'Blueprint',  'value' => e($item->blueprint->identifier)];
            $rows[] = ['label' => 'Status',     'value' => $this->statusBadge($item->status)];

            if ($item->current_workflow_step_id) {
                $item->loadMissing('workflowStep');
                $rows[] = ['label' => 'Workflow', 'value' => e($item->workflowStep?->name ?? '—')];
            }
        }

        if ($language) {
            $rows[] = ['label' => 'Language', 'value' => e($language->name) . ' <code>' . e($language->code) . '</code>'];
        }

        if ($site) {
            $rows[] = ['label' => 'Site', 'value' => e($site->name ?? $site->domain ?? '—')];
        }

        if ($item) {
            $allSlugs = $item->allSlugs();
            $slugLines = [];
            foreach ($allSlugs as $code => $paths) {
                foreach ($paths as $path) {
                    $type = $path['type'] === 'mount' ? ' 🔗' : '';
                    $slugLines[] = "<code>{$code}</code> " . e($path['path']) . $type;
                }
            }
            if ($slugLines) {
                $rows[] = ['label' => 'URLs', 'value' => implode('<br>', $slugLines)];
            }

            $cacheKey = "marble.item.{$item->id}";
            $rows[] = ['label' => 'Cache key', 'value' => "<code>{$cacheKey}</code>"];
        }

        // Build rows HTML
        $rowsHtml = '';
        foreach ($rows as $row) {
            $rowsHtml .= <<<HTML
            <tr>
                <td style="color:#999;padding:3px 10px 3px 0;white-space:nowrap;vertical-align:top;font-size:11px">{$row['label']}</td>
                <td style="padding:3px 0;font-size:11px">{$row['value']}</td>
            </tr>
HTML;
        }

        $editBtn = '';
        if ($item ?? false) {
            $editUrl = url("{$prefix}/item/edit/{$item->id}");
            $editBtn = <<<HTML
            <a href="{$editUrl}" target="_blank"
               style="display:inline-block;margin-top:8px;padding:4px 10px;background:#e67e22;color:#fff;text-decoration:none;border-radius:3px;font-size:11px;font-weight:bold">
                ✎ Edit in Marble
            </a>
HTML;
        }

        $adminUser = Auth::guard('marble')->user();
        $adminName = e($adminUser->email ?? '');

        return <<<HTML
<div id="marble-debugbar" style="position:fixed;bottom:16px;right:16px;z-index:99999;font-family:monospace;font-size:12px">
    <!-- Toggle button -->
    <div id="marble-debugbar-toggle"
         onclick="(function(){var p=document.getElementById('marble-debugbar-panel');p.style.display=p.style.display==='none'?'block':'none'})()"
         style="background:#2c3e50;color:#e67e22;padding:5px 10px;border-radius:4px;cursor:pointer;user-select:none;display:inline-block;box-shadow:0 2px 8px rgba(0,0,0,.4)">
        🔶 Marble
    </div>

    <!-- Panel -->
    <div id="marble-debugbar-panel"
         style="display:none;position:absolute;bottom:32px;right:0;background:#1e2a35;color:#ecf0f1;border-radius:6px;padding:14px 16px;min-width:300px;max-width:420px;box-shadow:0 4px 20px rgba(0,0,0,.6);border:1px solid #34495e">

        <!-- Header -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:8px;border-bottom:1px solid #34495e">
            <span style="color:#e67e22;font-weight:bold;font-size:13px">🔶 Marble Debugbar</span>
            <span style="color:#7f8c8d;font-size:10px">{$adminName}</span>
        </div>

        <table style="border-collapse:collapse;width:100%">
            {$rowsHtml}
        </table>

        {$editBtn}

        <!-- Close -->
        <div onclick="document.getElementById('marble-debugbar-panel').style.display='none'"
             style="position:absolute;top:8px;right:10px;cursor:pointer;color:#7f8c8d;font-size:14px">✕</div>
    </div>
</div>
HTML;
    }

    private function statusBadge(string $status): string
    {
        $colors = [
            'published' => '#27ae60',
            'draft'     => '#e67e22',
        ];
        $bg = $colors[$status] ?? '#7f8c8d';
        return "<span style='background:{$bg};color:#fff;padding:1px 6px;border-radius:3px;font-size:10px'>" . e($status) . "</span>";
    }
}
