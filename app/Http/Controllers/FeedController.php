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

        $this->validateRssUrl($validated['url']);

        $feed = FluxRss::create([
            'url'            => $validated['url'],
            'name'           => $validated['name'] ?? null,
            'id_utilisateur' => Auth::id(),
        ]);

        return response()->json($feed, 201);
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
        } elseif (isset($xml->entry)) {
            foreach ($xml->entry as $entry) {
                $link = '';
                foreach ($entry->link as $l) {
                    if (in_array((string) $l['rel'], ['alternate', ''])) {
                        $link = (string) $l['href'];
                        break;
                    }
                }
                $articles[] = $meta + [
                    'title'        => (string) $entry->title,
                    'url'          => $link,
                    'description'  => strip_tags((string) ($entry->summary ?? $entry->content ?? '')),
                    'published_at' => (string) $entry->updated,
                    'image'        => null,
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
