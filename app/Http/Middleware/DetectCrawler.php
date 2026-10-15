<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectCrawler
{
    /**
     * Liste des user-agents de crawlers connus.
     */
    protected array $crawlers = [
    // Réseaux sociaux
    'facebookexternalhit',
    'facebot',
    'twitterbot',
    'linkedinbot',
    'pinterest',
    'slackbot',
    'discordbot',
    'telegrambot',
    'skypeuripreview',
    'embedly',
    'quora link preview',
    'showyoubot',
    'outbrain',
    'vkshare',           // <-- tout en minuscules
    'w3c_validator',     // <-- tout en minuscules
    'redditbot',
    'applebot',          // <-- tout en minuscules
    'whatsapp',          // <-- tout en minuscules
    'whatsappbot',
    'whatsapp-desktop',

    // Moteurs de recherche
    'googlebot',
    'bingbot',
    'yandex',
    'duckduckbot',
    'baiduspider',

    // Outils de test / SEO
    'opengraph',
    'ahrefsbot',
    'semrushbot',
    'bot',               // mot générique en dernier recours
];
    public function handle(Request $request, Closure $next): Response
{
    $userAgent = strtolower((string) $request->header('User-Agent'));

    $isCrawler = false;
    foreach ($this->crawlers as $crawler) {
        if (str_contains($userAgent, strtolower($crawler))) {  // <-- strtolower ici
            $isCrawler = true;
            break;
        }
    }

    if (!$isCrawler) {
        abort(404);
    }

    $request->attributes->set('is_crawler', true);

    return $next($request);
}
}
