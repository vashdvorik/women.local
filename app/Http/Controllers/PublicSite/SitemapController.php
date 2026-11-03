<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Catalog;
use App\Models\Event;
use App\Models\Expert;
use App\Models\Post;
use App\Models\Project;
use App\Models\SiteOpportunity;
use App\Support\PublicCache;
use Illuminate\Http\Response;

/**
 * Карта сайта для поисковиков (/sitemap.xml) и robots.txt со ссылкой на неё. В карту попадают только открытые страницы
 * публичного сайта: разделы и опубликованные материалы из админки. Кабинет, админка, вход и платежи не входят.
 * Язык на сайте выбирается cookie, у каждой страницы один адрес, поэтому языковых вариантов (hreflang) нет.
 * Карта лежит в кеше публичных страниц и пересобирается вместе с ним после любой правки в админке.
 */
class SitemapController extends Controller
{
    /** Разделы без записей из админки: адрес маршрута и важность для поисковика. */
    private const PAGES = [
        'members' => 'weekly',
        'experts' => 'weekly',
        'events' => 'daily',
        'projects' => 'weekly',
        'opportunities' => 'daily',
        'media.photos' => 'weekly',
        'media.videos' => 'weekly',
        'media.publications' => 'weekly',
        'about' => 'monthly',
        'about.priorities' => 'monthly',
        'about.regulations' => 'monthly',
        'members.join' => 'monthly',
        'gala.regulations' => 'monthly',
        'partners' => 'monthly',
        'contact' => 'monthly',
    ];

    public function sitemap(): Response
    {
        $xml = PublicCache::remember('sitemap.xml', fn () => $this->build());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /app/',
            'Disallow: /r/',
            'Disallow: /payment/',
            'Disallow: /language/',
            'Disallow: /dev/',
            '',
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ];

        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function build(): string
    {
        $urls = [['loc' => url('/'), 'changefreq' => 'weekly', 'lastmod' => null]];

        foreach (self::PAGES as $route => $changefreq) {
            $urls[] = ['loc' => route($route), 'changefreq' => $changefreq, 'lastmod' => null];
        }

        $groups = [
            [Event::published()->whereNotNull('slug')->get()->filter->hasBody(), fn ($m) => route('events.show', ['event' => $m->slug])],
            [Project::where('is_published', true)->get()->filter->hasPage(), fn ($m) => route('projects.show', ['project' => $m->slug])],
            [SiteOpportunity::published()->onSite()->get(), fn ($m) => route('opportunities.show', ['opportunity' => $m->slug])],
            [Post::published()->onSite()->get(), fn ($m) => route('media.publications.show', ['post' => $m->slug])],
            [Album::published()->get(), fn ($m) => route('media.photos.show', ['album' => $m->slug])],
            [Catalog::published()->get()->filter->hasPages(), fn ($m) => route('media.catalogs.show', ['catalog' => $m->slug])],
        ];

        foreach ($groups as [$items, $href]) {
            foreach ($items as $item) {
                if (filled($item->slug)) {
                    $urls[] = ['loc' => $href($item), 'changefreq' => 'monthly', 'lastmod' => $item->updated_at?->toAtomString()];
                }
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= '  <url><loc>'.e($url['loc']).'</loc>'
                .($url['lastmod'] ? '<lastmod>'.$url['lastmod'].'</lastmod>' : '')
                .'<changefreq>'.$url['changefreq'].'</changefreq></url>'."\n";
        }

        return $xml.'</urlset>'."\n";
    }
}
