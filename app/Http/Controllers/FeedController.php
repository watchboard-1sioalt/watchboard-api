<?php

namespace App\Http\Controllers;

use App\Models\FluxRss;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class FeedController extends Controller
{
    public function index(): JsonResponse
    {
        $feeds = FluxRss::with('tags')->where('id_utilisateur', Auth::id())->get();

        return response()->json($feeds);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url'  => 'required|url|max:255',
            'name' => 'nullable|string|max:150',
        ]);

        $url = $validated['url'];

        if (preg_match('#(youtube\.com|youtu\.be)#i', $url)) {
            $url = $this->resolveYoutubeChannelRss($url);
        }

        $this->validateRssUrl($url);

        $feed = FluxRss::create([
            'url'            => $url,
            'name'           => $validated['name'] ?? null,
            'id_utilisateur' => Auth::id(),
        ]);

        return response()->json($feed, 201);
    }

    private function resolveYoutubeChannelRss(string $url): string
    {
        // Déjà une URL de flux YouTube
        if (str_contains($url, 'feeds/videos.xml')) {
            return $url;
        }

        // youtube.com/channel/UCxxxxxx — extraction directe sans requête
        if (preg_match('#youtube\.com/channel/(UC[\w-]+)#i', $url, $m)) {
            return 'https://www.youtube.com/feeds/videos.xml?channel_id=' . $m[1];
        }

        // Normalise vers www.youtube.com pour éviter les redirects manqués
        $url = preg_replace('#^https?://(www\.)?youtube\.com#i', 'https://www.youtube.com', $url);
        // Supprime le slash final
        $url = rtrim($url, '/');

        $body = $this->fetchYoutubePage($url);

        // YouTube injecte le lien RSS dans le <head>
        if (preg_match('#feeds/videos\.xml\?channel_id=(UC[\w-]+)#', $body, $m)) {
            return 'https://www.youtube.com/feeds/videos.xml?channel_id=' . $m[1];
        }

        // Fallbacks JSON embarqués
        if (preg_match('#"channelId"\s*:\s*"(UC[\w-]+)"#',  $body, $m) ||
            preg_match('#"externalId"\s*:\s*"(UC[\w-]+)"#', $body, $m) ||
            preg_match('#/channel/(UC[\w-]+)#',              $body, $m)) {
            return 'https://www.youtube.com/feeds/videos.xml?channel_id=' . $m[1];
        }

        abort(422, 'Impossible de trouver l\'identifiant de la chaîne YouTube.');
    }

    private function fetchYoutubePage(string $url): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_ENCODING       => '', // décode automatiquement gzip/br/deflate
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER     => [
                'Accept-Language: en-US,en;q=0.9',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
        ]);

        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        abort_if($body === false || $status < 200 || $status >= 300, 422, 'Impossible d\'accéder à la page YouTube.');

        return $body;
    }

    public function show(int $id): JsonResponse
    {
        $feed = FluxRss::with('tags')->where('id_fluxrss', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        return response()->json($feed);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $feed = FluxRss::where('id_fluxrss', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
        ]);

        $feed->update(['name' => $validated['name']]);

        return response()->json($feed);
    }

    public function destroy(int $id): JsonResponse
    {
        $feed = FluxRss::where('id_fluxrss', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        $feed->tags()->detach();
        $feed->delete();

        return response()->json(null, 204);
    }

    public function articles(int $id): JsonResponse
    {
        $feed = FluxRss::where('id_fluxrss', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        return response()->json($this->parseFeedArticles($feed));
    }

    public function allArticles(Request $request): JsonResponse
    {
        $query = FluxRss::where('id_utilisateur', Auth::id());

        if ($request->filled('flux_id')) {
            $query->where('id_fluxrss', $request->integer('flux_id'));
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('tag', $request->string('tag')));
        }

        $articles = [];
        foreach ($query->get() as $feed) {
            $articles = array_merge($articles, $this->parseFeedArticles($feed));
        }

        usort($articles, fn ($a, $b) => strcmp($b['published_at'] ?? '', $a['published_at'] ?? ''));

        return response()->json($articles);
    }

    public function attachTag(Request $request, int $id): JsonResponse
    {
        $feed = FluxRss::where('id_fluxrss', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        $validated = $request->validate(['tag_id' => 'required|integer|exists:tags,id_tag']);

        $feed->tags()->syncWithoutDetaching([$validated['tag_id']]);

        return response()->json(null, 204);
    }

    public function detachTag(int $id, int $tagId): JsonResponse
    {
        $feed = FluxRss::where('id_fluxrss', $id)
            ->where('id_utilisateur', Auth::id())
            ->firstOrFail();

        $feed->tags()->detach($tagId);

        return response()->json(null, 204);
    }

    private function parseFeedArticles(FluxRss $feed): array
    {
        $response = Http::timeout(10)->get($feed->url);

        if (! $response->successful()) {
            return [];
        }

        $xml = @simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            return [];
        }

        $articles = [];
        $meta = ['feed_id' => $feed->id_fluxrss, 'feed_name' => $feed->name ?? $feed->url];

        if (isset($xml->channel->item)) {
            $logo = isset($xml->channel->image->url)
                ? (string) $xml->channel->image->url
                : null;

            foreach ($xml->channel->item as $item) {
                $enclosure = $item->enclosure ?? null;
                $image = ($enclosure && isset($enclosure['url'])) ? (string) $enclosure['url'] : null;

                $articles[] = $meta + [
                    'title'        => (string) $item->title,
                    'url'          => (string) $item->link,
                    'description'  => strip_tags((string) $item->description),
                    'published_at' => (string) $item->pubDate,
                    'image'        => $image,
                    'feed_logo'    => $logo,
                ];
            }
        // Format RSS Atom pour les flux rss youtube
        } elseif (isset($xml->entry)) {
            $isYoutube = str_contains($feed->url, 'youtube.com/feeds/');

            foreach ($xml->entry as $entry) {
                $link = '';
                foreach ($entry->link as $l) {
                    if (in_array((string) $l['rel'], ['alternate', ''])) {
                        $link = (string) $l['href'];
                        break;
                    }
                }

                $image       = null;
                $description = strip_tags((string) ($entry->summary ?? $entry->content ?? ''));
                $publishedAt = (string) $entry->updated;

                if ($isYoutube) {
                    $media = $entry->children('http://search.yahoo.com/mrss/');
                    if (isset($media->group)) {
                        $groupMedia = $media->group->children('http://search.yahoo.com/mrss/');
                        $thumbAttrs = $groupMedia->thumbnail->attributes();
                        if (isset($thumbAttrs['url'])) {
                            $image = (string) $thumbAttrs['url'];
                        }
                        if (isset($groupMedia->description)) {
                            $description = strip_tags((string) $groupMedia->description);
                        }
                    }
                    $publishedAt = (string) ($entry->published ?: $entry->updated);
                }

                $articles[] = $meta + [
                    'title'        => (string) $entry->title,
                    'url'          => $link,
                    'description'  => $description,
                    'published_at' => $publishedAt,
                    'image'        => $image,
                    'feed_logo'    => null,
                ];
            }
        }

        return $articles;
    }

    private function validateRssUrl(string $url): void
    {
        $response = Http::timeout(10)->get($url);

        abort_if(! $response->successful(), 422, 'L\'URL est inaccessible.');

        $contentType = $response->header('Content-Type') ?? '';
        $body = $response->body();

        $isRss  = str_contains($contentType, 'rss') || str_contains($contentType, 'xml') || str_contains($contentType, 'atom');
        $hasRssTag = str_contains($body, '<rss') || str_contains($body, '<feed') || str_contains($body, '<channel');

        abort_if(! $isRss && ! $hasRssTag, 422, 'L\'URL ne pointe pas vers un flux RSS ou Atom valide.');
    }
}
