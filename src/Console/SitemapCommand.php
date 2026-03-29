<?php

namespace Marble\Admin\Console;

use Illuminate\Console\Command;
use Marble\Admin\Facades\Marble;
use Marble\Admin\Models\Item;
use Marble\Admin\Models\Language;

class SitemapCommand extends Command
{
    protected $signature = 'marble:sitemap {--output= : Output file path (default: public/sitemap.xml)} {--locale= : Language code to use for slugs}';
    protected $description = 'Generate a sitemap.xml from all published Marble items with slugs';

    public function handle(): int
    {
        $locale = $this->option('locale') ?? config('marble.primary_locale', 'en');
        Marble::setLocale($locale);

        $frontendUrl = rtrim(config('marble.frontend_url', ''), '/');

        $items = Item::where('status', 'published')
            ->whereHas('itemValues', function ($q) {
                $q->whereHas('blueprintField', fn ($q) => $q->where('identifier', 'slug'))
                  ->where('value', '!=', '');
            })
            ->with('blueprint', 'parent')
            ->get();

        $urls = [];
        foreach ($items as $item) {
            $slug = $item->slug();
            if ($slug) {
                $urls[] = $frontendUrl . $slug;
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= '  <url><loc>' . htmlspecialchars($url) . '</loc></url>' . "\n";
        }
        $xml .= '</urlset>';

        $output = $this->option('output') ?? public_path('sitemap.xml');
        file_put_contents($output, $xml);

        $this->info('Sitemap written to ' . $output . ' (' . count($urls) . ' URLs)');

        return self::SUCCESS;
    }
}
