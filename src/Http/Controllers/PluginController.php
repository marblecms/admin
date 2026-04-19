<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;

class PluginController extends Controller
{
    private const PACKAGIST_SEARCH = 'https://packagist.org/search.json';
    private const PACKAGIST_PACKAGE = 'https://packagist.org/packages/%s.json';
    private const REGISTRY_URL = 'https://raw.githubusercontent.com/marblecms/plugins/main/registry.json';

    public function index(Request $request)
    {
        $query    = trim($request->input('q', ''));
        $page     = max(1, (int) $request->input('page', 1));
        $packages = [];
        $total    = 0;
        $error    = null;
        $registry = $this->fetchRegistry();

        try {
            $params = [
                'type' => 'marble-plugin',
                'page' => $page,
            ];
            if ($query !== '') {
                $params['q'] = $query;
            }

            $response = Http::timeout(8)->get(self::PACKAGIST_SEARCH, $params);

            if ($response->successful()) {
                $data     = $response->json();
                $total    = $data['total'] ?? 0;
                $results  = $data['results'] ?? [];

                foreach ($results as $pkg) {
                    $name = $pkg['name'];
                    $packages[] = array_merge($pkg, [
                        'registry' => $registry[$name] ?? null,
                    ]);
                }
            } else {
                $error = 'Packagist returned HTTP ' . $response->status();
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return view('marble::plugins.index', compact('packages', 'total', 'query', 'page', 'error', 'registry'));
    }

    public function show(Request $request, string $vendor, string $package)
    {
        $name     = "{$vendor}/{$package}";
        $registry = $this->fetchRegistry();
        $data     = null;
        $error    = null;

        try {
            $response = Http::timeout(8)->get(sprintf(self::PACKAGIST_PACKAGE, $name));
            if ($response->successful()) {
                $data = $response->json('package');
            } else {
                $error = 'Package not found.';
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return view('marble::plugins.show', [
            'name'     => $name,
            'data'     => $data,
            'entry'    => $registry[$name] ?? null,
            'error'    => $error,
        ]);
    }

    private function fetchRegistry(): array
    {
        try {
            $response = Http::timeout(5)->get(self::REGISTRY_URL);
            if ($response->successful()) {
                return $response->json() ?? [];
            }
        } catch (\Exception) {
            // registry unavailable — silently degrade
        }
        return [];
    }
}
